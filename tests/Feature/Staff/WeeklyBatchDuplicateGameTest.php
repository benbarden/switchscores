<?php

namespace Tests\Feature\Staff;

use App\Domain\GameImport\GameImporter;
use App\Domain\GameTitleHash\HashGenerator;
use App\Domain\WeeklyBatch\ExistingGameMatcher;
use App\Models\Console;
use App\Models\Game;
use App\Models\GameTitleHash;
use App\Models\User;
use App\Models\WeeklyBatch;
use App\Models\WeeklyBatchItem;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

/**
 * Weekly update: games already in the DB are found, and never imported twice (#211).
 *
 * The case behind it: a game imported as "Steins;gate Re: boot" and renamed by hand to
 * "STEINS;GATE RE:BOOT" wasn't matched by the next paste, the item's title was edited to
 * the new name, and the import saved a duplicate game before its title hash failed.
 */
class WeeklyBatchDuplicateGameTest extends TestCase
{
    use DatabaseTransactions;

    private WeeklyBatch $batch;

    public function setUp(): void
    {
        parent::setUp();
        // batch_date is unique; a far-future date can't clash with a real batch
        $this->batch = WeeklyBatch::create([
            'batch_date' => '2099-'.str_pad((string) random_int(1, 12), 2, '0', STR_PAD_LEFT).'-'.str_pad((string) random_int(1, 28), 2, '0', STR_PAD_LEFT),
            'status' => WeeklyBatch::STATUS_SETUP,
        ]);
    }

    private function owner(): User
    {
        return new User([
            'display_name' => 'Weekly Duplicate Test',
            'email' => 'xx.weekly.duplicate.test.'.uniqid().'@switchscores.com',
            'is_staff' => 1,
            'is_owner' => 1,
        ]);
    }

    /**
     * A game imported under one title, renamed, with both hashes - as the game editor leaves it.
     */
    private function makeRenamedGame(string $suffix, int $consoleId = Console::ID_SWITCH_2): Game
    {
        $game = new Game();
        $game->title = 'ZQX;DUP RE:BOOT '.$suffix;
        $game->link_title = 'test-weekly-duplicate-'.uniqid();
        $game->console_id = $consoleId;
        $game->save();

        $hashGenerator = new HashGenerator();
        foreach (['Zqx;dup Re: boot '.$suffix, 'ZQX;DUP RE:BOOT '.$suffix] as $title) {
            GameTitleHash::create([
                'title' => strtolower($title),
                'title_hash' => $hashGenerator->generateHash($title),
                'game_id' => $game->id,
                'console_id' => $consoleId,
            ]);
        }
        return $game;
    }

    private function makeReadyItem(array $data): WeeklyBatchItem
    {
        $item = new WeeklyBatchItem();
        $item->fill($data + [
            'batch_id' => $this->batch->id,
            'console' => 'switch-2',
            'list_type' => 'upcoming',
            'page_number' => 1,
            'sort_order' => 0,
            'release_date' => '2099-06-01',
            'item_status' => WeeklyBatchItem::STATUS_READY,
        ]);
        $item->save();
        return $item;
    }

    private function matcher(): ExistingGameMatcher
    {
        return $this->app->make(ExistingGameMatcher::class);
    }

    public function testMatchesByNsuid()
    {
        $game = $this->makeRenamedGame('A');
        $game->eshop_europe_nsuid = '79990000000001';
        $game->save();

        $found = $this->matcher()->find('79990000000001', 'Something Else Entirely', null, Console::ID_SWITCH_2);

        $this->assertEquals($game->id, $found?->id);
    }

    public function testMatchesAnOldTitleHashAfterARename()
    {
        $game = $this->makeRenamedGame('B');

        $found = $this->matcher()->find(null, 'Zqx;dup Re: boot B', 'ZQX;DUP RE:BOOT B', Console::ID_SWITCH_2);

        $this->assertEquals($game->id, $found?->id);
    }

    public function testMatchesTheRawTitle()
    {
        $game = $this->makeRenamedGame('C');

        $found = $this->matcher()->find(null, 'Normalised Differently C', 'ZQX;DUP RE:BOOT C', Console::ID_SWITCH_2);

        $this->assertEquals($game->id, $found?->id);
    }

    public function testDoesNotMatchTheOtherConsole()
    {
        $this->makeRenamedGame('D', Console::ID_SWITCH_1);

        $found = $this->matcher()->find(null, 'ZQX;DUP RE:BOOT D', 'ZQX;DUP RE:BOOT D', Console::ID_SWITCH_2);

        $this->assertNull($found);
    }

    public function testImportSkipsAnItemWhoseEditedTitleIsAlreadyInTheDb()
    {
        $game = $this->makeRenamedGame('E');
        $item = $this->makeReadyItem(['title' => 'ZQX;DUP RE:BOOT E', 'title_raw' => 'ZQX;DUP RE:BOOT E']);
        $gamesBefore = Game::count();

        $response = $this->actingAs($this->owner())->post(route('staff.games.weekly-updates.list.import', [
            'batchId' => $this->batch->id, 'console' => 'switch-2', 'listType' => 'upcoming',
        ]));

        $response->assertSessionHas('success');
        $this->assertStringContainsString('Already in DB, not imported', session('success'));
        $this->assertSame($gamesBefore, Game::count());
        $item->refresh();
        $this->assertSame(WeeklyBatchItem::STATUS_ALREADY_IN_DB, $item->item_status);
        $this->assertEquals($game->id, $item->game_id);
    }

    public function testImportSavesTheNsuid()
    {
        $item = $this->makeReadyItem([
            'title' => 'Zqx Dup New Game '.uniqid(),
            'title_raw' => 'ZQX DUP NEW GAME',
            'nsuid' => '79990000000002',
        ]);

        $result = $this->app->make(GameImporter::class)->importItem($item->toImportGameData());

        $this->assertSame('79990000000002', $result['game']->fresh()->eshop_europe_nsuid);
    }

    public function testImporterLeavesNoGameBehindWhenTheTitleHashIsTaken()
    {
        $this->makeRenamedGame('F');
        $item = $this->makeReadyItem(['title' => 'ZQX;DUP RE:BOOT F', 'title_raw' => 'ZQX;DUP RE:BOOT F']);
        $gamesBefore = Game::count();

        try {
            $this->app->make(GameImporter::class)->importItem($item->toImportGameData());
            $this->fail('Expected the import to be refused');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('already in the DB', $e->getMessage());
        }

        $this->assertSame($gamesBefore, Game::count());
    }

    public function testBackfillCopiesTheNsuidFromImportedItems()
    {
        $game = $this->makeRenamedGame('G');
        $this->makeReadyItem([
            'title' => $game->title, 'title_raw' => $game->title,
            'nsuid' => '79990000000003',
            'item_status' => WeeklyBatchItem::STATUS_IMPORTED, 'game_id' => $game->id,
        ]);
        $matched = $this->makeRenamedGame('H');
        $this->makeReadyItem([
            'title' => $matched->title, 'title_raw' => $matched->title,
            'nsuid' => '79990000000004',
            'item_status' => WeeklyBatchItem::STATUS_ALREADY_IN_DB, 'game_id' => $matched->id,
        ]);

        $this->assertSame(0, Artisan::call('WeeklyBatchBackfillGameNsuids'));
        $this->assertNull($game->fresh()->eshop_europe_nsuid);

        $this->assertSame(0, Artisan::call('WeeklyBatchBackfillGameNsuids', ['--apply' => true]));
        $this->assertSame('79990000000003', $game->fresh()->eshop_europe_nsuid);
        $this->assertNull($matched->fresh()->eshop_europe_nsuid);
    }
}
