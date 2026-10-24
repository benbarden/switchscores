<?php

namespace Tests\Feature\Staff;

use App\Domain\WeeklyBatch\MissingFromPaste;
use App\Models\Console;
use App\Models\Game;
use App\Models\User;
use App\Models\WeeklyBatch;
use App\Models\WeeklyBatchItem;
use App\Models\WeeklyPasteMissingCheck;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Games in the DB missing from the latest weekly paste (#195). Upcoming only. Batches are dated
 * 2099 so they are always the most recent in the localdev data, and games are dated 2100 so
 * they belong on the Upcoming list.
 */
class WeeklyPasteMissingTest extends TestCase
{
    use DatabaseTransactions;

    private function owner(): User
    {
        return new User([
            'display_name' => 'Weekly Paste Missing Test',
            'email' => 'xx.weekly.paste.missing.test.'.uniqid().'@switchscores.com',
            'is_staff' => 1,
            'is_owner' => 1,
        ]);
    }

    private function makeGame(?string $euReleaseDate = '2100-03-01', int $isReleased = 0): Game
    {
        $game = new Game();
        $game->title = 'Zqx Paste Missing '.uniqid();
        $game->link_title = 'test-paste-missing-'.uniqid();
        $game->console_id = Console::ID_SWITCH_2;
        $game->eu_release_date = $euReleaseDate;
        $game->eu_is_released = $isReleased;
        $game->save();
        return $game;
    }

    private function makeBatch(string $batchDate): WeeklyBatch
    {
        return WeeklyBatch::create(['batch_date' => $batchDate, 'status' => WeeklyBatch::STATUS_SETUP]);
    }

    private function makeItem(WeeklyBatch $batch, ?Game $game, string $status = WeeklyBatchItem::STATUS_ALREADY_IN_DB): WeeklyBatchItem
    {
        return WeeklyBatchItem::create([
            'batch_id' => $batch->id,
            'console' => 'switch-2',
            'list_type' => 'upcoming',
            'page_number' => 1,
            'sort_order' => 0,
            'title' => $game ? $game->title : 'Zqx Paste Missing Other',
            'title_raw' => $game ? $game->title : 'Zqx Paste Missing Other',
            'release_date' => '2100-03-01',
            'item_status' => $status,
            'game_id' => $game?->id,
        ]);
    }

    private function rowFor(Game $game): ?object
    {
        return $this->app->make(MissingFromPaste::class)->get($game->id)[0] ?? null;
    }

    public function testListsAGameMissingFromTheLatestBatch()
    {
        $game = $this->makeGame();
        $older = $this->makeBatch('2099-01-01');
        $this->makeItem($older, $game);
        $this->makeItem($this->makeBatch('2099-01-08'), null);

        $row = $this->rowFor($game);

        $this->assertNotNull($row);
        $this->assertSame('2099-01-01', $row->last_seen);
        $this->assertSame('2099-01-08', $row->batch_date);
        $this->assertNull($row->checked_on);
    }

    public function testGamesInTheLatestBatchAreLeftOut()
    {
        $batch = $this->makeBatch('2099-01-01');
        $matched = $this->makeGame();
        $this->makeItem($batch, $matched);
        $outOfRange = $this->makeGame();
        $this->makeItem($batch, $outOfRange, WeeklyBatchItem::STATUS_OUT_OF_RANGE);

        $this->assertNull($this->rowFor($matched));
        $this->assertNull($this->rowFor($outOfRange));
    }

    public function testAnExcludedItemStillCountsAsSeen()
    {
        // Excluded items are never given a game_id; the 24 Oct STEINS;GATE items were excluded
        $game = $this->makeGame();
        $item = $this->makeItem($this->makeBatch('2099-01-01'), null, WeeklyBatchItem::STATUS_EXCLUDED);
        $item->title = $game->title;
        $item->save();

        $this->assertNull($this->rowFor($game));
    }

    public function testReleasedDelistedAndPastGamesAreLeftOut()
    {
        $this->makeItem($this->makeBatch('2099-01-01'), null);
        $released = $this->makeGame('2100-03-01', 1);
        $delisted = $this->makeGame();
        $delisted->game_status = \App\Enums\GameStatus::DELISTED;
        $delisted->save();
        $pastDate = $this->makeGame('2098-12-01');

        $this->assertNull($this->rowFor($released));
        $this->assertNull($this->rowFor($delisted));
        $this->assertNull($this->rowFor($pastDate));
    }

    public function testAGameWithNoDateIsListed()
    {
        $this->makeItem($this->makeBatch('2099-01-01'), null);

        $this->assertNotNull($this->rowFor($this->makeGame(null)));
    }

    public function testACheckLastsUntilTheGameIsSeenAgain()
    {
        $game = $this->makeGame();
        $this->makeItem($this->makeBatch('2099-01-01'), $game);
        $this->makeItem($this->makeBatch('2099-01-08'), null);
        WeeklyPasteMissingCheck::create(['game_id' => $game->id, 'checked_on' => '2099-01-09']);

        $this->assertSame('2099-01-09', $this->rowFor($game)->checked_on);
        $this->assertFalse($this->app->make(MissingFromPaste::class)->isMissingAndNotChecked($game->id));

        // Seen again on the 15th, missing again on the 22nd: the old check no longer counts
        $this->makeItem($this->makeBatch('2099-01-15'), $game);
        $this->makeItem($this->makeBatch('2099-01-22'), null);

        $this->assertNull($this->rowFor($game)->checked_on);
        $this->assertTrue($this->app->make(MissingFromPaste::class)->isMissingAndNotChecked($game->id));
    }

    public function testCheckAndUncheck()
    {
        $game = $this->makeGame();
        $this->makeItem($this->makeBatch('2099-01-01'), null);

        $this->actingAs($this->owner())->post(
            route('staff.data-sources.differences.weekly-paste.missing.check'),
            ['game_id' => $game->id]
        )->assertSessionHas('pasteMissingSuccess');

        $this->assertNotNull($this->rowFor($game)->checked_on);

        $this->actingAs($this->owner())->post(
            route('staff.data-sources.differences.weekly-paste.missing.uncheck'),
            ['game_id' => $game->id]
        );

        $this->assertNull($this->rowFor($game)->checked_on);
    }

    public function testPageShowsTheGame()
    {
        $game = $this->makeGame();
        $this->makeItem($this->makeBatch('2099-01-01'), null);

        $response = $this->actingAs($this->owner())->get(route('staff.data-sources.differences.weekly-paste.missing'));

        $response->assertStatus(200);
        $response->assertSee($game->title);
        $response->assertSee('2099-01-01');
    }
}
