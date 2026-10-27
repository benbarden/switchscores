<?php

namespace App\Console\Commands\Game;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\Domain\JobRun\CronRunContext;
use App\Domain\Steam\Repository as SteamRepository;
use App\Domain\Steam\SyncService as SteamSyncService;

class SyncSteamReviews extends Command
{
    protected $signature = 'game:sync-steam-reviews
                            {--limit=50 : Number of games to process per run}
                            {--delay=2 : Seconds to wait between API requests}';

    protected $description = 'Fetch and store Steam review summaries for all linked games.';

    public function __construct(
        private SteamRepository $repoSteam,
        private SteamSyncService $steamSync
    )
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $logger = Log::channel('cron');

        $logger->info(' *************** '.$this->getName().' *************** ');

        $limit = (int) $this->option('limit');
        $delay = (int) $this->option('delay');
        $games = $this->repoSteam->getLinkedGames($limit);

        if ($games->isEmpty()) {
            $this->info('No games linked to Steam.');
            $logger->info('No games linked to Steam.');
            return self::SUCCESS;
        }

        $this->info('Syncing Steam reviews for ' . $games->count() . ' game(s)...');

        $success = 0;
        $failed = 0;
        $failedIds = [];
        $reasons = [];

        foreach ($games as $game) {
            $result = $this->steamSync->syncGame($game->id, $game->steam_id);

            if ($result) {
                $this->info('  [' . $game->id . '] ' . $game->title
                    . ' — ' . $result->review_score_desc
                    . ' (' . number_format($result->total_reviews) . ' reviews)');
                $success++;
            } else {
                $reason = $this->steamSync->lastError() ?? 'unknown';
                $this->warn('  [' . $game->id . '] ' . $game->title . ' — failed: ' . $reason);
                $logger->warning('Steam sync failed for game ' . $game->id . ' (Steam ID ' . $game->steam_id . '): ' . $reason);
                $failed++;
                $failedIds[] = $game->id;
                $reasons[$reason] = true;
            }

            if ($delay > 0) {
                sleep($delay);
            }
        }

        $this->info('Done. Success: ' . $success . ', Failed: ' . $failed . '.');
        $logger->info('Synced ' . $games->count() . ' game(s). Success: ' . $success . ', Failed: ' . $failed . '.');

        if (!$failedIds) {
            return self::SUCCESS;
        }

        $summary = 'Steam sync failed for ' . $failed . ' of ' . $games->count() . ' game(s): ' . implode(', ', $failedIds)
            . '. Reasons: ' . implode('; ', array_keys($reasons));

        // Every game failing means the sync itself is broken (as in #219), not a few bad Steam IDs,
        // so it's a failed run on cron health rather than a warning (#220).
        if ($success === 0) {
            $this->error($summary);
            $logger->error($summary);
            return self::FAILURE;
        }

        CronRunContext::warn($summary);
        return self::SUCCESS;
    }
}
