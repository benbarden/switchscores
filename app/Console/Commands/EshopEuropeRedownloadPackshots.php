<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\Services\GameService;
use App\Services\EshopEuropeGameService;
use App\Services\Eshop\PackshotEurope;

class EshopEuropeRedownloadPackshots extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'EshopEuropeRedownloadPackshots';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Re-downloads all packshots from the European eShop.';

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

        $gameService = resolve('Services\GameService');
        /* @var GameService $gameService */
        $eshopEuropeGameService = resolve('Services\EshopEuropeGameService');
        /* @var EshopEuropeGameService $eshopEuropeGameService */

        $eshopPackshotEuropeService = new PackshotEurope();

        $logger->info('Loading data...');

        $gameList = $eshopEuropeGameService->getAllWithLink();

        if (count($gameList) == 0) {
            $logger->warn('No items found; exiting');
            return;
        }

        $logger->info('Found '.count($gameList).' item(s)');

        foreach ($gameList as $item) {

            $game = $gameService->find($item->game_id);

            if (!$game) {
                $logger->warn('Error loading data');
                $logger->info('**************************************************');
                continue;
            }

            $gameId = $game->id;
            $gameTitle = $game->title;

            $fsId = $game->eshop_europe_fs_id;

            if (!$fsId) {
                $logger->warn($gameTitle.': record is not linked to an fs_id; skipping');
                $logger->info('**************************************************');
                continue;
            }

            $eshopEuropeGame = $eshopEuropeGameService->getByFsId($fsId);

            if (!$eshopEuropeGame) {
                $logger->warn($gameTitle.': record is linked to fs_id: '.$fsId.' - record could not be located; skipping');
                continue;
            }

            // Square
            $eshopPackshotEuropeService->downloadSquarePackshot($eshopEuropeGame, $game);
            $destFilename = $eshopPackshotEuropeService->getDestFilename();
            if ($eshopPackshotEuropeService->getIsAborted() == false) {
                $game->boxart_square_url = $destFilename;
                $game->save();
                $logger->info('Square packshot saved!: '.$destFilename);
                $logger->info('**************************************************');
            }

            // Header
            $eshopPackshotEuropeService->downloadHeaderImage($eshopEuropeGame, $game);
            $destFilename = $eshopPackshotEuropeService->getDestFilename();
            if ($eshopPackshotEuropeService->getIsAborted() == false) {
                $game->boxart_header_image = $destFilename;
                $game->save();
                $logger->info('Header packshot saved!: '.$destFilename);
                $logger->info('**************************************************');
            }
        }
    }
}
