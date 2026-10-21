<?php

namespace Tests\Unit\Domain\GameGroup;

use App\Domain\GameGroup\Repository as GameGroupRepository;
use App\Models\Console;
use App\Models\Game;
use App\Models\GameGroup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Linking and unlinking games in a group (#44). Uses its own games inside the test
 * transaction, so nothing in the localdev data is touched.
 */
class RepositoryTest extends TestCase
{
    use DatabaseTransactions;

    private GameGroupRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = new GameGroupRepository();
    }

    private function makeGame(string $title, int $consoleId): Game
    {
        $game = new Game();
        $game->title = $title;
        $game->link_title = 'test-game-group-'.uniqid();
        $game->console_id = $consoleId;
        $game->save();
        return $game;
    }

    private function makeLinkedPair(): array
    {
        $group = $this->repo->create('Test Group Game', GameGroup::MATCH_IDENTICAL);
        $switch1 = $this->makeGame('Test Group Game', Console::ID_SWITCH_1);
        $switch2 = $this->makeGame('Test Group Game (Switch 2)', Console::ID_SWITCH_2);
        $this->repo->linkGame($group, $switch1);
        $this->repo->linkGame($group, $switch2);
        return [$group, $switch1, $switch2];
    }

    public function testLinkGamesToAGroup()
    {
        [$group, $switch1, $switch2] = $this->makeLinkedPair();

        $this->assertSame(2, $group->games()->count());
        $this->assertSame($group->id, $switch1->fresh()->gameGroup->id);
        $this->assertSame(GameGroup::MATCH_IDENTICAL, $switch2->fresh()->gameGroup->match_type);
    }

    public function testUnlinkingFromAPairRemovesTheGroup()
    {
        [$group, $switch1, $switch2] = $this->makeLinkedPair();

        $this->repo->unlinkGame($switch1->fresh());

        $this->assertNull($switch1->fresh()->game_group_id);
        $this->assertNull($switch2->fresh()->game_group_id);
        $this->assertNull($this->repo->find($group->id));
    }

    public function testUnlinkingFromALargerGroupKeepsIt()
    {
        [$group, $switch1, $switch2] = $this->makeLinkedPair();
        $third = $this->makeGame('Test Group Game Third', Console::ID_SWITCH_1);
        $this->repo->linkGame($group, $third);

        $this->repo->unlinkGame($third->fresh());

        $this->assertNull($third->fresh()->game_group_id);
        $this->assertSame(2, $group->games()->count());
    }

    public function testUnlinkingAnUngroupedGameDoesNothing()
    {
        $game = $this->makeGame('Test Group Game Alone', Console::ID_SWITCH_1);

        $this->repo->unlinkGame($game);

        $this->assertNull($game->fresh()->game_group_id);
    }
}
