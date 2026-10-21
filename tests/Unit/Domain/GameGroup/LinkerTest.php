<?php

namespace Tests\Unit\Domain\GameGroup;

use App\Domain\GameGroup\LinkException;
use App\Domain\GameGroup\Linker;
use App\Domain\GameGroup\Repository as GameGroupRepository;
use App\Domain\GameGroup\TitleMatchKey;
use App\Models\Console;
use App\Models\Game;
use App\Models\GameGroup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Linking two games from staff game detail (#44). Uses its own games inside the test
 * transaction, so nothing in the localdev data is touched.
 */
class LinkerTest extends TestCase
{
    use DatabaseTransactions;

    private GameGroupRepository $repo;
    private Linker $linker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = new GameGroupRepository();
        $this->linker = new Linker($this->repo, new TitleMatchKey());
    }

    private function makeGame(string $title, int $consoleId): Game
    {
        $game = new Game();
        $game->title = $title;
        $game->link_title = 'test-game-linker-'.uniqid();
        $game->console_id = $consoleId;
        $game->save();
        return $game;
    }

    public function testTwoUngroupedGamesGetANewGroupNamedAfterSwitch1()
    {
        $switch2 = $this->makeGame('Linker Test Game: Nintendo Switch 2 Edition', Console::ID_SWITCH_2);
        $switch1 = $this->makeGame('Linker Test Game', Console::ID_SWITCH_1);

        $group = $this->linker->link($switch2, $switch1, GameGroup::MATCH_ENHANCED);

        $this->assertSame('Linker Test Game', $group->name);
        $this->assertSame(GameGroup::MATCH_ENHANCED, $group->match_type);
        $this->assertSame($group->id, $switch1->fresh()->game_group_id);
        $this->assertSame($group->id, $switch2->fresh()->game_group_id);
    }

    public function testJoiningAnExistingGroupKeepsItsMatchType()
    {
        $group = $this->repo->create('Linker Test Game', GameGroup::MATCH_IDENTICAL);
        $switch1 = $this->makeGame('Linker Test Game', Console::ID_SWITCH_1);
        $this->repo->linkGame($group, $switch1);
        $switch2 = $this->makeGame('Linker Test Game (Switch 2)', Console::ID_SWITCH_2);

        $result = $this->linker->link($switch2, $switch1->fresh(), GameGroup::MATCH_ENHANCED);

        $this->assertSame($group->id, $result->id);
        $this->assertSame(GameGroup::MATCH_IDENTICAL, $result->match_type);
        $this->assertSame($group->id, $switch2->fresh()->game_group_id);
    }

    public function testSameConsoleIsRefused()
    {
        $a = $this->makeGame('Linker Test A', Console::ID_SWITCH_1);
        $b = $this->makeGame('Linker Test B', Console::ID_SWITCH_1);

        $this->expectException(LinkException::class);
        $this->linker->link($a, $b, GameGroup::MATCH_IDENTICAL);
    }

    public function testLinkingToItselfIsRefused()
    {
        $a = $this->makeGame('Linker Test A', Console::ID_SWITCH_1);

        $this->expectException(LinkException::class);
        $this->linker->link($a, $a, GameGroup::MATCH_IDENTICAL);
    }

    public function testGroupAlreadyHoldingThatConsoleIsRefused()
    {
        $switch1 = $this->makeGame('Linker Test Game', Console::ID_SWITCH_1);
        $switch2 = $this->makeGame('Linker Test Game (Switch 2)', Console::ID_SWITCH_2);
        $this->linker->link($switch1, $switch2, GameGroup::MATCH_IDENTICAL);
        $anotherSwitch2 = $this->makeGame('Linker Test Game Deluxe (Switch 2)', Console::ID_SWITCH_2);

        $this->expectException(LinkException::class);
        $this->linker->link($anotherSwitch2, $switch1->fresh(), GameGroup::MATCH_IDENTICAL);
    }

    public function testGamesInDifferentGroupsAreRefused()
    {
        $a1 = $this->makeGame('Linker Test A', Console::ID_SWITCH_1);
        $a2 = $this->makeGame('Linker Test A (Switch 2)', Console::ID_SWITCH_2);
        $b1 = $this->makeGame('Linker Test B', Console::ID_SWITCH_1);
        $b2 = $this->makeGame('Linker Test B (Switch 2)', Console::ID_SWITCH_2);
        $this->linker->link($a1, $a2, GameGroup::MATCH_IDENTICAL);
        $this->linker->link($b1, $b2, GameGroup::MATCH_IDENTICAL);

        $this->expectException(LinkException::class);
        $this->linker->link($a1->fresh(), $b2->fresh(), GameGroup::MATCH_IDENTICAL);
    }

    public function testUnknownMatchTypeIsRefused()
    {
        $switch1 = $this->makeGame('Linker Test Game', Console::ID_SWITCH_1);
        $switch2 = $this->makeGame('Linker Test Game (Switch 2)', Console::ID_SWITCH_2);

        $this->expectException(LinkException::class);
        $this->linker->link($switch1, $switch2, 'sort-of');
    }
}
