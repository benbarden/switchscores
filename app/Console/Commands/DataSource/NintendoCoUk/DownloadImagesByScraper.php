<?php

namespace App\Console\Commands\DataSource\NintendoCoUk;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\Domain\GameLists\Repository as RepoGameLists;
use App\Domain\Game\Repository as RepoGame;

use App\Domain\Scraper\NintendoCoUkPackshot;
use App\Factories\DataSource\NintendoCoUk\DownloadImageFactory;
use App\Domain\DataSource\NintendoCoUk\PackshotUrlBuilder;

class DownloadImagesByScraper extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'DSNintendoCoUkDownloadImagesByScraper {gameId?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Downloads packshots for games using the scraper.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        private RepoGame $repoGame,
        private PackshotUrlBuilder $packshotUrlBuilder,
        private NintendoCoUkPackshot $scraperNintendoCoUk
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

        if ($argGameId) {

            $gameItem = $this->repoGame->find($argGameId);
            if (!$gameItem) {
                $logger->error('Cannot find game: '.$argGameId);
                exit;
            }

            $gameList = [$gameItem];

            $logger->info('Using override game id');

        } else {

            $repoGameLists = new RepoGameLists();
            $gameList = $repoGameLists->noNintendoCoUkIdWithStoreOverride(100);

            $logger->info('Found '.count($gameList).' item(s); processing');

        }

        $logger->info('Loading data...');

        foreach ($gameList as $game) {

            $gameId = $game->id;
            $gameTitle = $game->title;
            $storeUrl = $game->nintendo_store_url_override;
            $squareUrlOverride = $game->packshot_square_url_override;

            $squareUrl = $game->image_square;
            $headerUrl = $game->image_header;

            if (!$squareUrl || !$headerUrl) {

                $logger->info('Processing item: '.$gameTitle.' [ID: '.$gameId.']');

                try {
                    $this->scraperNintendoCoUk->crawlPage($storeUrl);
                    $squareUrl = $this->scraperNintendoCoUk->getSquareUrl();
                    $headerUrl = $this->scraperNintendoCoUk->getHeaderUrl();
                    // If we have an override, the generated URL probably errored.
                    // In which case, let's just use that straight away.
                    if ($squareUrlOverride) {
                        $logger->info('Found packshot_square_url_override');
                        $squareUrl = $squareUrlOverride;
                    }
                    // Fallback for missing square images
                    // We want to do this AFTER the square URL override, to avoid regex failures
                    if ($headerUrl && !$squareUrl) {
                        $squareUrl = $this->packshotUrlBuilder->getSquareUrl($headerUrl);
                    }
                    // Download away!
                    if ($squareUrl || $headerUrl) {
                        DownloadImageFactory::downloadFromStoreUrl($game, $squareUrl, $headerUrl, $logger);
                    }
                } catch (\Exception $e) {
                    $logger->error($e->getMessage());
                    continue;
                }

            }

        }

        $logger->info('Complete');
    }
}
