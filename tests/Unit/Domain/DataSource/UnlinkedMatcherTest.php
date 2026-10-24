<?php

namespace Tests\Unit\Domain\DataSource;

use App\Domain\DataSource\NintendoCoUk\UnlinkedMatcher;
use App\Domain\GameGroup\TitleMatchKey;
use App\Enums\GameStatus;
use App\Models\Console;
use App\Models\DataSource;
use App\Models\DataSourceParsed;
use App\Models\Game;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Suggesting games for unlinked API items (#192). Runs over localdev's real games too, so
 * each test uses its own unlikely titles and passes in only its own items.
 */
class UnlinkedMatcherTest extends TestCase
{
    use DatabaseTransactions;

    private function makeGame(string $title, int $consoleId, ?int $linkId = null): Game
    {
        $game = new Game();
        $game->title = $title;
        $game->link_title = 'test-unlinked-matcher-'.uniqid();
        $game->console_id = $consoleId;
        $game->eshop_europe_fs_id = $linkId;
        $game->save();
        return $game;
    }

    private function makeItem(string $title, int $consoleId): DataSourceParsed
    {
        return DataSourceParsed::create([
            'source_id' => DataSource::DSID_NINTENDO_CO_UK,
            'console_id' => $consoleId,
            'link_id' => random_int(2000000000, 2100000000),
            'title' => $title,
            'release_date_eu' => '2026-10-28',
        ]);
    }

    private function suggest(DataSourceParsed ...$items): array
    {
        return (new UnlinkedMatcher(new TitleMatchKey()))->suggest(collect($items));
    }

    public function testMatchesTheUnlinkedGameOnTheSameConsole()
    {
        $this->makeGame('Zqx Matcher Test', Console::ID_SWITCH_1);
        $switch2 = $this->makeGame('Zqx Matcher Test', Console::ID_SWITCH_2);
        $item = $this->makeItem('Zqx Matcher Test', Console::ID_SWITCH_2);

        $result = $this->suggest($item);

        $this->assertCount(1, $result['matches']);
        $this->assertSame($switch2->id, $result['matches'][0]['game']->id);
    }

    public function testMatchesTheApiSuffixForm()
    {
        $game = $this->makeGame('Zqx Matcher Test: Nintendo Switch 2 Edition', Console::ID_SWITCH_2);
        $item = $this->makeItem("Zqx Matcher Test \u{2013} Nintendo Switch\u{2122} 2 Edition", Console::ID_SWITCH_2);

        $result = $this->suggest($item);

        $this->assertSame($game->id, $result['matches'][0]['game']->id);
    }

    public function testSeveralUnlinkedGames()
    {
        $this->makeGame('Zqx Matcher Test', Console::ID_SWITCH_2);
        $this->makeGame('Zqx Matcher Test', Console::ID_SWITCH_2);
        $item = $this->makeItem('Zqx Matcher Test', Console::ID_SWITCH_2);

        $result = $this->suggest($item);

        $this->assertCount(0, $result['matches']);
        $this->assertCount(2, $result['several'][0]['games']);
    }

    public function testNoMatchGivesTheOtherConsoleHint()
    {
        $switch1 = $this->makeGame('Zqx Matcher Test', Console::ID_SWITCH_1);
        $item = $this->makeItem('Zqx Matcher Test', Console::ID_SWITCH_2);

        $result = $this->suggest($item);

        $this->assertCount(0, $result['matches']);
        $this->assertNull($result['none'][0]['linkedGame']);
        $this->assertSame($switch1->id, $result['none'][0]['otherConsoleGame']->id);
    }

    public function testAGameLinkedElsewhereIsNotSuggested()
    {
        $linked = $this->makeGame('Zqx Matcher Test', Console::ID_SWITCH_2, 2100000001);
        $item = $this->makeItem('Zqx Matcher Test', Console::ID_SWITCH_2);

        $result = $this->suggest($item);

        $this->assertCount(0, $result['matches']);
        $this->assertSame($linked->id, $result['none'][0]['linkedGame']->id);
    }

    public function testSoftDeletedGamesAreIgnored()
    {
        $game = $this->makeGame('Zqx Matcher Test', Console::ID_SWITCH_2);
        $game->game_status = GameStatus::SOFT_DELETED;
        $game->save();
        $item = $this->makeItem('Zqx Matcher Test', Console::ID_SWITCH_2);

        $result = $this->suggest($item);

        $this->assertCount(0, $result['matches']);
        $this->assertCount(1, $result['none']);
    }
}
