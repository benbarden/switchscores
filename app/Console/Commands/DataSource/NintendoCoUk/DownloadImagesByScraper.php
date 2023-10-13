<?php

namespace App\Console\Commands\DataSource\NintendoCoUk;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\Domain\GameLists\Repository as RepoGameLists;
use App\Domain\Game\Repository as RepoGame;

use App\Domain\Scraper\NintendoCoUkPackshot;
use App\Factories\DataSource\NintendoCoUk\DownloadImageFactory;
use App\Domain\DataSource\NintendoCoUk\PackshotUrlBuilder;

use App\Traits\SwitchServices;

class DownloadImagesByScraper extends Command
{
    use SwitchServices;

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
    public function __construct()
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

            $repoGame = new RepoGame();
            $gameItem = $repoGame->find($argGameId);
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

        $packshotBuilder = new PackshotUrlBuilder();

        foreach ($gameList as $game) {

            $gameId = $game->id;
            $gameTitle = $game->title;
            $storeUrl = $game->nintendo_store_url_override;
            $squareUrlOverride = $game->packshot_square_url_override;

            $squareUrl = $game->image_square;
            $headerUrl = $game->image_header;

            if (!$squareUrl || !$headerUrl) {

                $logger->info('Processing item: '.$gameTitle.' [ID: '.$gameId.']');

                $scraper = new NintendoCoUkPackshot();

                try {
                    $scraper->crawlPage($storeUrl);
                    $squareUrl = $scraper->getSquareUrl();
                    $headerUrl = $scraper->getHeaderUrl();
                    if ($headerUrl && !$squareUrl) {
                        // fallback for missing square images
                        $squareUrl = $packshotBuilder->getSquareUrl($headerUrl);
                    }
                    // If we have an override, the generated URL probably errored.
                    // In which case, let's just use that straight away.
                    if ($squareUrlOverride) {
                        $logger->info('Found packshot_square_url_override');
                        $squareUrl = $squareUrlOverride;
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

        /*
        foreach ($dsParsedList as $dsItem) {

            $itemTitle = $dsItem->title;
            $gameId = $dsItem->game_id;
            $game = $this->getServiceGame()->find($gameId);

            if (!$game) {
                $logger->error($itemTitle.' - invalid game_id: '.$gameId.' - skipping');
                continue;
            }

            $logger->info('Download images for game: '.$itemTitle.' ['.$gameId.']');
            DownloadImageFactory::downloadImages($game, $dsItem);

        }
        */

        $logger->info('Complete');
    }
}
