<?php

namespace App\Console\Commands\DataSource\NintendoCoUk;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\Domain\Game\Repository as RepoGame;

use App\Domain\DataSource\NintendoCoUk\DownloadPackshotHelper;

class DownloadPackshots extends Command
{
    const MODE_REFRESH_ALL = 'refreshAll';
    const MODE_NOT_DOWNLOADED = 'notDownloaded';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'DSNintendoCoUkDownloadPackshots {gameId?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Downloads packshots for games. (Will replace the 2 DownloadImages scripts)';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        private RepoGame $repoGame
    )
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $argGameId = $this->argument('gameId');

        $logger = Log::channel('cron');

        $logger->info(' *************** '.$this->signature.' *************** ');

        $downloadPackshotHelper = new DownloadPackshotHelper($logger);

        if ($argGameId) {

            $gameItem = $this->repoGame->find($argGameId);
            if (!$gameItem) {
                $logger->error('Cannot find game: '.$argGameId);
                exit;
            }

            $logger->info('Using override game id');

            $downloadPackshotHelper->downloadForGame($gameItem);

        } else {

            $logger->info('');
            $logger->info('========== PACKSHOTS BY DATA SOURCE ==========');
            $downloadPackshotHelper->downloadAllWithDataSourceId();

            $logger->info('');
            $logger->info('========== PACKSHOTS BY OVERRIDE URL ==========');
            $downloadPackshotHelper->downloadAllWithOverrideUrl();

        }

        $logger->info('Complete');
    }
}
