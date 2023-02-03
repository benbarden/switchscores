<?php

namespace App\Console\Commands\DataSource\NintendoCoUk;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\Domain\GameLists\Repository as RepoGameLists;

use App\Domain\Scraper\NintendoCoUkPackshot;
use App\Factories\DataSource\NintendoCoUk\DownloadImageFactory;

use App\Traits\SwitchServices;

class DownloadImagesByScraper extends Command
{
    use SwitchServices;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'DSNintendoCoUkDownloadImagesByScraper';

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
        $logger = Log::channel('cron');

        $logger->info(' *************** '.$this->signature.' *************** ');

        $logger->info('Loading data...');

        $repoGameLists = new RepoGameLists();
        $gameList = $repoGameLists->noNintendoCoUkIdWithStoreOverride(100);

        $logger->info('Found '.count($gameList).' item(s); processing');

        foreach ($gameList as $game) {

            $gameId = $game->id;
            $gameTitle = $game->title;
            $storeUrl = $game->nintendo_store_url_override;

            $squareUrl = $game->image_square;
            $headerUrl = $game->image_header;

            if (!$squareUrl && !$headerUrl) {

                $logger->info('Processing item: '.$gameTitle.' [ID: '.$gameId.']');

                $scraper = new NintendoCoUkPackshot();

                try {
                    $scraper->crawlPage($storeUrl);
                    $squareUrl = $scraper->getSquareUrl();
                    $headerUrl = $scraper->getHeaderUrl();
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
