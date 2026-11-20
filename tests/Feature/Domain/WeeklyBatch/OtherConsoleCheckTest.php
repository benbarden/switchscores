<?php

namespace Tests\Feature\Domain\WeeklyBatch;

use App\Domain\WeeklyBatch\OtherConsoleCheck;
use App\Models\Console;
use App\Models\DataSource;
use App\Models\DataSourceParsed;
use App\Models\DataSourceRaw;
use App\Models\Game;
use App\Models\WeeklyBatchItem;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Warnings for Switch 2 rows that are really Switch 1 games: a store page whose API record
 * lists both systems, or a store URL in the Switch 1 games section. Items aren't saved; the
 * check only reads their console and URL.
 */
class OtherConsoleCheckTest extends TestCase
{
    use DatabaseTransactions;

    private function pageId(): string
    {
        // Store page IDs are 7 digits; 9 can't clash with a real one
        return (string) random_int(900000000, 999999999);
    }

    private function item(int $id, string $console, string $url): WeeklyBatchItem
    {
        $item = new WeeklyBatchItem(['console' => $console, 'nintendo_url' => $url, 'title' => 'Zqx Other Console']);
        $item->id = $id;
        return $item;
    }

    private function apiRecord(string $pageId, array $systems, ?int $gameId = null): void
    {
        DataSourceRaw::create([
            'source_id' => DataSource::DSID_NINTENDO_CO_UK,
            'console_id' => Console::ID_SWITCH_2,
            'link_id' => $pageId,
            'title' => 'Zqx Other Console',
            'source_data_json' => json_encode(['system_names_txt' => $systems]),
        ]);
        DataSourceParsed::create([
            'source_id' => DataSource::DSID_NINTENDO_CO_UK,
            'console_id' => Console::ID_SWITCH_2,
            'link_id' => (int) $pageId,
            'title' => 'Zqx Other Console',
            'game_id' => $gameId,
        ]);
    }

    private function switch1Game(): Game
    {
        $game = new Game();
        $game->title = 'Zqx Other Console Switch 1';
        $game->link_title = 'test-other-console-'.uniqid();
        $game->console_id = Console::ID_SWITCH_1;
        $game->save();
        return $game;
    }

    private function warnings(WeeklyBatchItem ...$items): array
    {
        return app(OtherConsoleCheck::class)->warnings(collect($items));
    }

    public function testADualTaggedStorePageNamesTheSwitch1Game()
    {
        $pageId = $this->pageId();
        $game = $this->switch1Game();
        $this->apiRecord($pageId, ['Nintendo Switch', 'Nintendo Switch 2'], $game->id);

        $warnings = $this->warnings($this->item(1, 'switch-2', "https://www.nintendo.com/en-gb/Games/Nintendo-Switch-2-games/Zqx-{$pageId}.html"));

        $this->assertSame([1 => "Store page is the Switch 1 game {$game->title} ({$game->id})"], $warnings);
    }

    public function testADualTaggedStorePageWithNoLinkedGameWarnsOutsideTheSwitch2Sections()
    {
        $pageId = $this->pageId();
        $this->apiRecord($pageId, ['Nintendo Switch', 'Nintendo Switch 2']);

        $warnings = $this->warnings($this->item(1, 'switch-2', "https://www.nintendo.com/en-gb/Games/Nintendo-Switch-download-software/Zqx-{$pageId}.html"));

        $this->assertSame([1 => 'Store page is tagged for Switch 1 and Switch 2'], $warnings);
    }

    public function testASwitch2EditionWithBothTagsDoesNotWarn()
    {
        $editionPage = $this->pageId();
        $this->apiRecord($editionPage, ['Nintendo Switch', 'Nintendo Switch 2']);
        $linkedPage = $this->pageId();
        $game = $this->switch1Game();
        $game->console_id = Console::ID_SWITCH_2;
        $game->save();
        $this->apiRecord($linkedPage, ['Nintendo Switch', 'Nintendo Switch 2'], $game->id);

        $warnings = $this->warnings(
            $this->item(1, 'switch-2', "https://www.nintendo.com/en-gb/Games/Nintendo-Switch-2-Edition/Zqx-{$editionPage}.html"),
            $this->item(2, 'switch-2', "https://www.nintendo.com/en-gb/Games/Nintendo-Switch-download-software/Zqx-{$linkedPage}.html"),
        );

        $this->assertSame([], $warnings);
    }

    public function testAStorePageInTheSwitch1SectionWarns()
    {
        $warnings = $this->warnings($this->item(1, 'switch-2', 'https://www.nintendo.com/en-gb/Games/Nintendo-Switch-games/Zqx-'.$this->pageId().'.html'));

        $this->assertSame([1 => 'Store page is in the Switch 1 games section'], $warnings);
    }

    public function testASwitch2OnlyGameDoesNotWarn()
    {
        $pageId = $this->pageId();
        $this->apiRecord($pageId, ['Nintendo Switch 2']);

        $warnings = $this->warnings($this->item(1, 'switch-2', "https://www.nintendo.com/en-gb/Games/Nintendo-Switch-2-games/Zqx-{$pageId}.html"));

        $this->assertSame([], $warnings);
    }

    public function testSwitch1ListsAreNotChecked()
    {
        $pageId = $this->pageId();
        $this->apiRecord($pageId, ['Nintendo Switch', 'Nintendo Switch 2']);

        $warnings = $this->warnings($this->item(1, 'switch-1', "https://www.nintendo.com/en-gb/Games/Nintendo-Switch-games/Zqx-{$pageId}.html"));

        $this->assertSame([], $warnings);
    }
}
