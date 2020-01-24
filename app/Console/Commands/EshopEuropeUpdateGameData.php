<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\Services\Eshop\Europe\UpdateGameData;

use App\Traits\SwitchServices;

class EshopEuropeUpdateGameData extends Command
{
    use SwitchServices;

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
     * @throws \Exception
     */
    public function handle()
    {
        $logger = Log::channel('cron');

        $logger->info(' *************** '.$this->signature.' *************** ');

        $serviceGame = $this->getServiceGame();
        $serviceEshopEuropeGame = $this->getServiceEshopEuropeGame();

        $logger->info('Clearing previous alerts...');

        $this->getServiceEshopEuropeAlert()->clearAll();

        $logger->info('Loading data...');

        $eshopList = $serviceEshopEuropeGame->getAllWithLink();

        foreach ($eshopList as $eshopItem) {

            $fsId = $eshopItem->fs_id;
            $eshopTitle = $eshopItem->title;
            $eshopUrl = $eshopItem->url;

            $game = $serviceGame->getByFsId('eu', $fsId);

            if (!$game) {
                $logger->error($eshopTitle.' - no game linked to fs_id: '.$fsId.'; skipping');
                continue;
            }

            $gameId = $game->id;

            if (!$eshopUrl) {
                $logger->error($eshopTitle.' - no URL found for this record. Skipping');
                continue;
            }

            // IMPORT RULES
            $gameImportRule = $this->getServiceGameImportRuleEshop()->getByGameId($gameId);

            // SETUP
            $serviceUpdateGameData = new UpdateGameData();
            $serviceUpdateGameData->setEshopItem($eshopItem);
            $serviceUpdateGameData->setGame($game);
            if ($gameImportRule) {
                $serviceUpdateGameData->setGameImportRule($gameImportRule);
            }
            $serviceUpdateGameData->resetLogMessages();

            // STORE METHOD NAMES FOR LOOPING LATER
            $updateGameDataMethods = [
                'updateNoOfPlayers',
                'updatePublisher',
                'updatePrice',
                'updateReleaseDate',
                'updateGenres',
            ];

            // UPDATES
            foreach ($updateGameDataMethods as $method) {

                call_user_func([$serviceUpdateGameData, $method]);

                $eshopAlert = $serviceUpdateGameData->getEshopAlert();
                if ($eshopAlert != null) {
                    $eshopAlert->save();
                }

                if ($serviceUpdateGameData->getLogMessageError()) {

                    $logger->error($serviceUpdateGameData->getLogMessageError());

                } elseif ($serviceUpdateGameData->getLogMessageWarning()) {

                    $logger->warn($serviceUpdateGameData->getLogMessageWarning());

                } elseif ($serviceUpdateGameData->getLogMessageInfo()) {

                    $logger->info($serviceUpdateGameData->getLogMessageInfo());

                }

                $serviceUpdateGameData->resetLogMessages();

            }

            // ***************************************************** //

            if ($serviceUpdateGameData->hasGameChanged()) {
                $game->save();
            }
        }
    }
}
