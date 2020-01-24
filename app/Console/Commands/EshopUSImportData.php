<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\Log;

use App\Services\Eshop\US\Loader;

use App\Traits\SwitchServices;

class EshopUSImportData extends Command
{
    use SwitchServices;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'EshopUSImportData';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports data from the US eShop.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    private $totalItemCount;

    private function loadLiveData(Logger $logger, Loader $eshopLoader, $offset = 0)
    {
        $itemCount = 200;

        $eshopLoader->clearResponseData();
        $logger->info('Loading data... (offset: '.$offset.')');
        $eshopLoader->loadGames($offset, $itemCount);
        $logger->info('Used request url: '.$eshopLoader->getRequestUrl());

        try {
            $responseArray = $eshopLoader->getResponseData();
        } catch (\Exception $e) {
            $logger->error('Got exception: '.$e->getMessage());
            return false;
        }

        if (!is_array($responseArray['games'])) {
            $logger->warn('No more game data to load');
            return false;
        } elseif (!array_key_exists('game', $responseArray['games'])) {
            $logger->warn('No more game data to load');
            return false;
        }

        if ($offset >= 5) {
            //$logger->warn('Triggering failsafe to avoid hitting API too much during testing');
            //return false;
        }

        $firstItemTitle = $responseArray['games']['game'][0]['title'];
        $logger->info('First item title: '.$firstItemTitle);

        $logger->info('Importing data...');
        $eshopLoader->importToDb();
        $importedItemCount = $eshopLoader->getImportedCount();
        $logger->info('Imported '.$importedItemCount.' item(s).');
        $this->totalItemCount += $importedItemCount;

        $offset = $offset + $itemCount;
        $this->loadLiveData($logger, $eshopLoader, $offset);
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

        $logger->info('Clearing previous data');

        \DB::statement("TRUNCATE TABLE eshop_us_games");

        $eshopLoader = new Loader();

        try {

            $loadComplete = false;

            $jsonLocalFile = dirname(__FILE__).'/../../../storage/eshop/us-test-all-games2.json';

            if ((\App::environment() == 'local') && (file_exists($jsonLocalFile))) {

                $logger->info('Loading local data from JSON file');
                $eshopLoader->loadLocalData($jsonLocalFile);
                $eshopLoader->importToDb();
                $importedItemCount = $eshopLoader->getImportedCount();
                $logger->info('Complete! Imported '.$importedItemCount.' item(s).');
                $this->totalItemCount = $importedItemCount;

            } else {

                // LIVE data load. Need to do it in stages.
                $logger->warn('Loading LIVE data from eShop. Do not abuse!');
                $this->totalItemCount = 0;
                $this->loadLiveData($logger, $eshopLoader);

            }

        } catch (\Exception $e) {
            $logger->error($e->getMessage());
        }
    }
}
