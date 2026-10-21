<?php

namespace Tests\Feature\PublicSite;

use App\Domain\GameGroup\Repository as GameGroupRepository;
use App\Enums\GameStatus;
use App\Models\Console;
use App\Models\Game;
use App\Models\GameGroup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * The "Also on" box on the public game page (#44): the game group first, then the
 * link_title match. Only an enhanced link gets a note.
 */
class GameAlsoOnTest extends TestCase
{
    use DatabaseTransactions;

    private function makeGame(string $title, int $consoleId, ?string $linkTitle = null): Game
    {
        $game = new Game();
        $game->title = $title;
        $game->link_title = $linkTitle ?? 'test-also-on-'.uniqid();
        $game->console_id = $consoleId;
        $game->game_status = GameStatus::ACTIVE;
        $game->save();
        return $game;
    }

    private function pageFor(Game $game)
    {
        $url = $game->console_id == Console::ID_SWITCH_2
            ? route('game.show.switch2', ['id' => $game->id, 'linkTitle' => $game->link_title])
            : route('game.show', ['id' => $game->id, 'linkTitle' => $game->link_title]);
        return $this->get($url);
    }

    private function group(string $matchType, Game ...$games): void
    {
        $repo = new GameGroupRepository();
        $group = $repo->create('Also On Test', $matchType);
        foreach ($games as $game) {
            $repo->linkGame($group, $game);
        }
    }

    public function testEnhancedGroupNotesBothSides()
    {
        $switch1 = $this->makeGame('Also On Test Game', Console::ID_SWITCH_1);
        $switch2 = $this->makeGame('Also On Test Game - Nintendo Switch 2 Edition', Console::ID_SWITCH_2);
        $this->group(GameGroup::MATCH_ENHANCED, $switch1, $switch2);

        $page = $this->pageFor($switch1);
        $page->assertStatus(200);
        $page->assertSee('Also on Switch 2');
        $page->assertSee('Enhanced Switch 2 Edition');

        $page = $this->pageFor($switch2);
        $page->assertSee('Also on Switch 1');
        $page->assertSee('Original Switch 1 version');
    }

    public function testIdenticalGroupHasNoNote()
    {
        $switch1 = $this->makeGame('Also On Test Game', Console::ID_SWITCH_1);
        $switch2 = $this->makeGame('Also On Test Game (Switch 2)', Console::ID_SWITCH_2);
        $this->group(GameGroup::MATCH_IDENTICAL, $switch1, $switch2);

        $page = $this->pageFor($switch1);
        $page->assertSee('Also on Switch 2');
        $page->assertDontSee('Enhanced Switch 2 Edition');
        $page->assertDontSee('Original Switch 1 version');
    }

    public function testUngroupedGamesStillMatchOnLinkTitle()
    {
        $linkTitle = 'test-also-on-fallback-'.uniqid();
        $switch1 = $this->makeGame('Also On Fallback Game', Console::ID_SWITCH_1, $linkTitle);
        $this->makeGame('Also On Fallback Game', Console::ID_SWITCH_2, $linkTitle);

        $page = $this->pageFor($switch1);
        $page->assertSee('Also on Switch 2');
        $page->assertDontSee('Enhanced Switch 2 Edition');
    }
}
