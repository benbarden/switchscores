<?php

namespace Tests\Unit\Domain\Steam;

use App\Domain\Steam\Repository;
use App\Enums\SteamStatus;
use App\Models\Game;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * The Steam reviews sync queue: never-attempted games first, then the oldest attempt,
 * so a game that has just failed goes to the back instead of coming up first again.
 */
class RepositoryTest extends TestCase
{
    use DatabaseTransactions;

    private function makeLinkedGame(?string $lastAttempted): Game
    {
        $game = new Game();
        $game->title = 'Zqx Steam Queue '.uniqid();
        $game->link_title = 'test-steam-queue-'.uniqid();
        $game->steam_id = (string) random_int(9000000, 9999999);
        $game->steam_status = SteamStatus::LINKED;
        $game->save();

        if ($lastAttempted) {
            Game::where('id', $game->id)->update(['steam_last_attempted_at' => $lastAttempted]);
        }

        return $game;
    }

    /**
     * Positions of these games in the whole queue. Other linked games are in the table too,
     * so the test compares relative order rather than exact positions.
     */
    private function queuePositions(Game ...$games): array
    {
        $queueIds = (new Repository())->getLinkedGames(1000000)->pluck('id')->all();

        return array_map(fn (Game $game) => array_search($game->id, $queueIds, true), $games);
    }

    public function testNeverAttemptedFirstThenOldestAttempt()
    {
        $recent = $this->makeLinkedGame(now()->subHour());
        $never = $this->makeLinkedGame(null);
        $old = $this->makeLinkedGame(now()->subDays(30));

        [$recentPos, $neverPos, $oldPos] = $this->queuePositions($recent, $never, $old);

        $this->assertNotFalse($recentPos);
        $this->assertLessThan($oldPos, $neverPos);
        $this->assertLessThan($recentPos, $oldPos);
    }

    public function testAnAttemptMovesAGameBehindTheOthers()
    {
        $failing = $this->makeLinkedGame(now()->subDays(60));
        $other = $this->makeLinkedGame(now()->subDays(30));

        [$failingPos, $otherPos] = $this->queuePositions($failing, $other);
        $this->assertLessThan($otherPos, $failingPos);

        (new Repository())->markAttempted($failing->id);

        [$failingPos, $otherPos] = $this->queuePositions($failing, $other);
        $this->assertLessThan($failingPos, $otherPos);
    }
}
