<?php

namespace App\Console\Commands\Game;

use Illuminate\Console\Command;

use App\Domain\Steam\Repository as SteamRepository;
use App\Domain\Steam\SyncService as SteamSyncService;

class SyncSteamReviews extends Command
{
    protected $signature = 'game:sync-steam-reviews
                            {--delay=2 : Seconds to wait between API requests}';

    protected $description = 'Fetch and store Steam review summaries for all linked games.';

    public function __construct(
        private SteamRepository $repoSteam,
        private SteamSyncService $steamSync
    )
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $delay = (int) $this->option('delay');
        $games = $this->repoSteam->getLinkedGames();

        if ($games->isEmpty()) {
            $this->info('No games linked to Steam.');
            return;
        }

        $this->info('Syncing Steam reviews for ' . $games->count() . ' game(s)...');

        $success = 0;
        $failed = 0;

        foreach ($games as $game) {
            $result = $this->steamSync->syncGame($game->id, $game->steam_id);

            if ($result) {
                $this->info('  [' . $game->id . '] ' . $game->title
                    . ' — ' . $result->review_score_desc
                    . ' (' . number_format($result->total_reviews) . ' reviews)');
                $success++;
            } else {
                $this->warn('  [' . $game->id . '] ' . $game->title . ' — failed');
                $failed++;
            }

            if ($delay > 0) {
                sleep($delay);
            }
        }

        $this->info('Done. Success: ' . $success . ', Failed: ' . $failed . '.');
    }
}
