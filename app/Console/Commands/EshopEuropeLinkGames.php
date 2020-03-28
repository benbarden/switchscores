<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\Traits\SwitchServices;

class EshopEuropeLinkGames extends Command
{
    use SwitchServices;

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
        $logger = Log::channel('cron');

        $logger->info(' *************** '.$this->signature.' *************** ');

        $logger->info('Loading data...');

        $serviceGame = $this->getServiceGame();
        $serviceEshopEuropeGame = $this->getServiceEshopEuropeGame();

        $gameData = $serviceGame->getAllWithoutEshopId('eu');

        $logger->info('Found records: '.count($gameData));

        try {

            foreach ($gameData as $game) {

                $fsId = null;
                $title = $game->title;

                $eshopGame = $serviceEshopEuropeGame->getByTitle($title);

                if (!$eshopGame) {
                    //$logger->warn('No match for title: '.$title);
                    continue;
                }

                $fsId = $eshopGame->fs_id;

                if ($game->eshop_europe_fs_id != null) {
                    if ($game->eshop_europe_fs_id == $fsId) {
                        //$logger->warn($title.' - already linked to '.$fsId.'; skipping');
                        continue;
                    }
                }

                $logger->info('Found title: '.$title.' - updating game with fs_id: '.$fsId);

                $game->eshop_europe_fs_id = $fsId;
                $game->save();

            }

        } catch (\Exception $e) {
            $logger->error($e->getMessage());
        }
    }
}
