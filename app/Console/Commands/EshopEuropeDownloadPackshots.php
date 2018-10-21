<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Services\GameService;
use App\Services\EshopEuropeGameService;

class EshopEuropeDownloadPackshots extends Command
{
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
        $this->info(' *** '.$this->signature.' ['.date('Y-m-d H:i:s').']'.' *** ');

        $gameService = resolve('Services\GameService');
        /* @var GameService $gameService */
        $eshopEuropeGameService = resolve('Services\EshopEuropeGameService');
        /* @var EshopEuropeGameService $eshopEuropeGameService */

        $this->info('Loading data...');

        $gameList = $gameService->getActionListNintendoUrlNoPackshots('eu');

        if (count($gameList) == 0) {
            $this->warn('No items found; exiting');
            return;
        }

        $this->info('Found '.count($gameList).' item(s)');

        foreach ($gameList as $item) {

            $game = $gameService->find($item->id);

            $gameId = $game->id;
            $gameTitle = $game->title;
            $gameLinkTitle = $game->link_title;
            $gamePackshot = $game->boxart_url;
            $gamePackshotSquare = $game->boxart_square_url;

            $fsId = $game->eshop_europe_fs_id;

            if ($gamePackshot || $gamePackshotSquare) {
                //$this->warn($gameTitle.': record already has a packshot; skipping');
                continue;
            }

            if (!$fsId) {
                //$this->warn($gameTitle.': record is not linked to an fs_id; skipping');
                continue;
            }

            $eshopEuropeGame = $eshopEuropeGameService->getByFsId($fsId);

            if (!$eshopEuropeGame) {
                $this->warn($gameTitle.': record is linked to fs_id: '.$fsId.' - record could not be located; skipping');
                continue;
            }

            $imageUrlSquare = $eshopEuropeGame->image_url_sq_s;
            $this->info('Downloading image: '.$imageUrlSquare);
            $imageData = file_get_contents('https:'.$imageUrlSquare);

            $fileExt = pathinfo($imageUrlSquare, PATHINFO_EXTENSION);
            $destFilename = 'sq-'.$gameLinkTitle.'.'.$fileExt;
            $storagePath = storage_path().'/tmp/';
            $this->info('Saving to: '.$storagePath.$destFilename);
            file_put_contents($storagePath.$destFilename, $imageData);

            $publicImagePath = public_path().'/img/games/square/';
            $this->info('Moving to: '.$publicImagePath.$destFilename);
            rename($storagePath.$destFilename, $publicImagePath.$destFilename);

            $game->boxart_square_url = $destFilename;
            $game->save();
            $this->info('Packshot saved!');
            $this->info('**************************************************');
        }
    }
}
