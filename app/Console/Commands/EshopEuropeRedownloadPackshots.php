<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

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
        $this->info(' *** '.$this->signature.' ['.date('Y-m-d H:i:s').']'.' *** ');

        $gameService = resolve('Services\GameService');
        /* @var GameService $gameService */
        $eshopEuropeGameService = resolve('Services\EshopEuropeGameService');
        /* @var EshopEuropeGameService $eshopEuropeGameService */

        $eshopPackshotEuropeService = new PackshotEurope();

        $this->info('Loading data...');

        $gameList = $eshopEuropeGameService->getAllWithLink();

        if (count($gameList) == 0) {
            $this->warn('No items found; exiting');
            return;
        }

        $this->info('Found '.count($gameList).' item(s)');

        foreach ($gameList as $item) {

            $game = $gameService->find($item->game_id);

            if (!$game) {
                $this->warn('Error loading data');
                $this->info('**************************************************');
                continue;
            }

            $gameId = $game->id;
            $gameTitle = $game->title;

            $fsId = $game->eshop_europe_fs_id;

            if (!$fsId) {
                $this->warn($gameTitle.': record is not linked to an fs_id; skipping');
                $this->info('**************************************************');
                continue;
            }

            $eshopEuropeGame = $eshopEuropeGameService->getByFsId($fsId);

            if (!$eshopEuropeGame) {
                $this->warn($gameTitle.': record is linked to fs_id: '.$fsId.' - record could not be located; skipping');
                continue;
            }

            $eshopPackshotEuropeService->downloadPackshot($eshopEuropeGame, $game);
            $destFilename = $eshopPackshotEuropeService->getDestFilename();
            if ($eshopPackshotEuropeService->getIsAborted() == false) {
                $game->boxart_square_url = $destFilename;
                $game->save();
                $this->info('Packshot saved!: '.$destFilename);
                $this->info('**************************************************');
            }
        }
    }
}