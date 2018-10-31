<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Services\GameService;
use App\Services\EshopEuropeGameService;

class EshopEuropeUpdateGameData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'EshopEuropeUpdateGameData';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates data for games linked to eShop Europe data records.';

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

        $eshopList = $eshopEuropeGameService->getAllWithLink();

        foreach ($eshopList as $eshopItem) {

            $fsId = $eshopItem->fs_id;
            $title = $eshopItem->title;
            $url = $eshopItem->url;

            if (!$url) {
                $this->error($title.' - no URL found for this record. Skipping');
                continue;
            }

            $game = $gameService->getByFsId('eu', $fsId);

            if (!$game) {
                $this->error($title.' - no game linked to fs_id: '.$fsId.'; skipping');
                continue;
            }

            $gameTitle = $game->title;

            // Update Nintendo page url
            if ($game->nintendo_page_url == null) {
                // No URL set, so let's update it
                $this->info($gameTitle.' - no existing nintendo_page_url. Updating...');
                $game->nintendo_page_url = $url;
                $game->save();
            } elseif ($game->nintendo_page_url != $url) {
                // URL set to something else
                //$this->warn($gameTitle.' - No change made. Game URL already set to: '.$game->nintendo_page_url.' - eShop record has: '.$url);
            } else {
                // It's the same, so nothing to do
                //$this->warn($gameTitle.' - URL set and matches eShop data. Nothing to do.');
            }
        }
    }
}
