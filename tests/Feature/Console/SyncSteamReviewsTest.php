<?php

namespace Tests\Feature\Console;

use App\Domain\JobRun\CronRunContext;
use App\Domain\Steam\Repository as SteamRepository;
use App\Domain\Steam\SyncService as SteamSyncService;
use App\Models\Game;
use App\Models\SteamReviewData;
use Illuminate\Support\Facades\Artisan;
use Mockery\MockInterface;
use Tests\TestCase;

/**
 * game:sync-steam-reviews: a few failed games are a warning with their reasons, but every
 * game failing is a failed run, because that means the sync itself is broken (as in #219).
 * Steam and the queue are mocked, so nothing is called or written.
 */
class SyncSteamReviewsTest extends TestCase
{
    private function queueOf(int $count): void
    {
        $games = collect(range(1, $count))->map(function (int $i) {
            $game = new Game();
            $game->id = 900000 + $i;
            $game->title = 'Steam test '.$i;
            $game->steam_id = (string) (100 + $i);
            return $game;
        });

        $this->mock(SteamRepository::class, function (MockInterface $mock) use ($games) {
            $mock->shouldReceive('getLinkedGames')->andReturn($games);
        });
    }

    private function syncReturns(array $results): void
    {
        $this->mock(SteamSyncService::class, function (MockInterface $mock) use ($results) {
            $mock->shouldReceive('syncGame')->andReturn(...$results);
            $mock->shouldReceive('lastError')->andReturn('HTTP 429 Too Many Requests');
        });
    }

    private function synced(): SteamReviewData
    {
        return new SteamReviewData(['review_score_desc' => 'Very Positive', 'total_reviews' => 1234]);
    }

    public function testEveryGameFailingIsAFailedRun()
    {
        $this->queueOf(3);
        $this->syncReturns([null, null, null]);

        $exitCode = Artisan::call('game:sync-steam-reviews', ['--delay' => 0]);

        $this->assertSame(1, $exitCode);
        $output = Artisan::output();
        $this->assertStringContainsString('failed for 3 of 3 game(s): 900001, 900002, 900003', $output);
        $this->assertStringContainsString('Reasons: HTTP 429 Too Many Requests', $output);
    }

    public function testSomeGamesFailingIsAWarningWithTheReason()
    {
        $this->queueOf(3);
        $this->syncReturns([$this->synced(), null, $this->synced()]);

        CronRunContext::start();
        $exitCode = Artisan::call('game:sync-steam-reviews', ['--delay' => 0]);
        $warnings = CronRunContext::finish();

        $this->assertSame(0, $exitCode);
        $this->assertSame(
            ['Steam sync failed for 1 of 3 game(s): 900002. Reasons: HTTP 429 Too Many Requests'],
            $warnings
        );
        $this->assertStringContainsString('[900002] Steam test 2 — failed: HTTP 429 Too Many Requests', Artisan::output());
    }

    public function testAllSyncedIsACleanRun()
    {
        $this->queueOf(2);
        $this->syncReturns([$this->synced(), $this->synced()]);

        CronRunContext::start();
        $exitCode = Artisan::call('game:sync-steam-reviews', ['--delay' => 0]);

        $this->assertSame(0, $exitCode);
        $this->assertSame([], CronRunContext::finish());
    }
}
