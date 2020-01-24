<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\Services\Eshop\PackshotEurope;

use App\Traits\SwitchServices;

class EshopEuropeDownloadPackshots extends Command
{
    use SwitchServices;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'EshopEuropeDownloadPackshots';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Finds packshots from the European eShop, downloads them and links them to games.';

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

        $serviceGame = $this->getServiceGame();
        $serviceEshopEuropeGame = $this->getServiceEshopEuropeGame();

        $servicePackshotEurope = new PackshotEurope();

        $logger->info('Loading data...');

        $gameList = $serviceGame->getEshopEuropeNoPackshots();

        if (count($gameList) == 0) {
            $logger->warn('No items found; exiting');
            return;
        }

        $logger->info('Found '.count($gameList).' item(s)');

        foreach ($gameList as $item) {

            $game = $serviceGame->find($item->id);

            $gameId = $game->id;
            $gameTitle = $game->title;
            $gamePackshotSquare = $game->boxart_square_url;
            $gamePackshotHeader = $game->boxart_header_image;

            $fsId = $game->eshop_europe_fs_id;

            if (($gamePackshotSquare) && ($gamePackshotHeader)) {
                //$logger->warn($gameTitle.': record already has a packshot; skipping');
                continue;
            }

            if (!$fsId) {
                //$logger->warn($gameTitle.': record is not linked to an fs_id; skipping');
                continue;
            }

            $eshopEuropeGame = $serviceEshopEuropeGame->getByFsId($fsId);

            if (!$eshopEuropeGame) {
                $logger->warn($gameTitle.': record is linked to fs_id: '.$fsId.' - record could not be located; skipping');
                continue;
            }

            try {

                // Square packshot
                $servicePackshotEurope->downloadSquarePackshot($eshopEuropeGame, $game);
                $destFilename = $servicePackshotEurope->getDestFilename();
                $logger->info('Saving square packshot: '.$destFilename);
                $game->boxart_square_url = $destFilename;

                // Header
                $servicePackshotEurope->downloadHeaderImage($eshopEuropeGame, $game);
                $destFilename = $servicePackshotEurope->getDestFilename();
                $logger->info('Saving header packshot: '.$destFilename);
                $game->boxart_header_image = $destFilename;

            } catch (\Exception $e) {

                $logger->error($e->getMessage());
                continue;

            }

            $game->save();
            $logger->info('Packshot(s) saved!');
            $logger->info('**************************************************');
        }
    }
}
