<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\Services\Eshop\PackshotEurope;

use App\Traits\SwitchServices;

class EshopEuropeRedownloadPackshots extends Command
{
    use SwitchServices;

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

        $serviceGame = $this->getServiceGame();
        $serviceEshopEuropeGame = $this->getServiceEshopEuropeGame();

        $servicePackshotEurope = new PackshotEurope();

        $logger->info('Loading data...');

        $gameList = $serviceEshopEuropeGame->getAllWithLink();

        if (count($gameList) == 0) {
            $logger->warn('No items found; exiting');
            return;
        }

        $logger->info('Found '.count($gameList).' item(s)');

        foreach ($gameList as $item) {

            $game = $serviceGame->find($item->game_id);

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

            $eshopEuropeGame = $serviceEshopEuropeGame->getByFsId($fsId);

            if (!$eshopEuropeGame) {
                $logger->warn($gameTitle.': record is linked to fs_id: '.$fsId.' - record could not be located; skipping');
                continue;
            }

            try {

                // Square
                $servicePackshotEurope->downloadSquarePackshot($eshopEuropeGame, $game);
                $destFilename = $servicePackshotEurope->getDestFilename();
                if ($servicePackshotEurope->getIsAborted() == false) {
                    $game->boxart_square_url = $destFilename;
                    $game->save();
                    $logger->info('Square packshot saved!: '.$destFilename);
                    $logger->info('**************************************************');
                }

                // Header
                $servicePackshotEurope->downloadHeaderImage($eshopEuropeGame, $game);
                $destFilename = $servicePackshotEurope->getDestFilename();
                if ($servicePackshotEurope->getIsAborted() == false) {
                    $game->boxart_header_image = $destFilename;
                    $game->save();
                    $logger->info('Header packshot saved!: '.$destFilename);
                    $logger->info('**************************************************');
                }

            } catch (\Exception $e) {

                $logger->error($e->getMessage());
                continue;

            }
        }
    }
}
