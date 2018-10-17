<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Game;
use App\Services\GameService;
use App\Services\GameTitleHashService;
use App\Services\EshopEuropeGameService;

class EshopEuropeLinkGames extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'EshopEuropeLinkGames';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Attempts to link data from the European eShop to games in the WOS database.';

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

        $this->info('Loading data...');

        $gameService = resolve('Services\GameService');
        /* @var GameService $gameService */
        $eshopEuropeGameService = resolve('Services\EshopEuropeGameService');
        /* @var EshopEuropeGameService $eshopEuropeGameService */
        $gameTitleHashService = resolve('Services\GameTitleHashService');
        /* @var GameTitleHashService $gameTitleHashService */

        $gameData = $gameService->getAllWithoutEshopId('eu');

        $this->info('Found records: '.count($gameData));

        try {

            foreach ($gameData as $game) {

                $title = $game->title;

                $eshopGame = $eshopEuropeGameService->getByTitle($title);

                if (!$eshopGame) {
                    $this->warn('No match for title: '.$title);
                    continue;
                }

                if ($game->eshop_europe_fs_id != null) {
                    $this->error('Wait, why is this here? '.$title.'|'.$game->eshop_europe_fs_id.'|'.$eshopGame->fs_id);
                    continue;
                }

                $fsId = $eshopGame->fs_id;

                $this->info('Found title: '.$title.' - updating game with fs_id: '.$fsId);

                $game->eshop_europe_fs_id = $fsId;
                $game->save();

            }

        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
