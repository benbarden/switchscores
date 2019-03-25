<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Services\Eshop\US\Loader;

class EshopUSImportData extends Command
{
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

    private function loadLiveData(Loader $eshopLoader, $offset = 0)
    {
        $itemCount = 200;

        $eshopLoader->clearResponseData();
        $this->info('Loading data... (offset: '.$offset.')');
        $eshopLoader->loadGames($offset, $itemCount);
        $this->info('Used request url: '.$eshopLoader->getRequestUrl());

        try {
            $responseArray = $eshopLoader->getResponseData();
        } catch (\Exception $e) {
            $this->error('Got exception: '.$e->getMessage());
            return false;
        }

        if (!is_array($responseArray['games'])) {
            $this->warn('No more game data to load');
            return false;
        } elseif (!array_key_exists('game', $responseArray['games'])) {
            $this->warn('No more game data to load');
            return false;
        }

        if ($offset >= 5) {
            //$this->warn('Triggering failsafe to avoid hitting API too much during testing');
            //return false;
        }

        $firstItemTitle = $responseArray['games']['game'][0]['title'];
        $this->info('First item title: '.$firstItemTitle);

        $this->info('Importing data...');
        $eshopLoader->importToDb();
        $importedItemCount = $eshopLoader->getImportedCount();
        $this->info('Imported '.$importedItemCount.' item(s).');
        $this->totalItemCount += $importedItemCount;

        $offset = $offset + $itemCount;
        $this->loadLiveData($eshopLoader, $offset);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info(' *** '.$this->signature.' ['.date('Y-m-d H:i:s').']'.' *** ');

        $this->info('Clearing previous data');

        \DB::statement("TRUNCATE TABLE eshop_us_games");

        $eshopLoader = new Loader();

        try {

            $loadComplete = false;

            $jsonLocalFile = dirname(__FILE__).'/../../../storage/eshop/us-test-all-games2.json';

            if ((\App::environment() == 'local') && (file_exists($jsonLocalFile))) {

                $this->info('Loading local data from JSON file');
                $eshopLoader->loadLocalData($jsonLocalFile);
                $eshopLoader->importToDb();
                $importedItemCount = $eshopLoader->getImportedCount();
                $this->info('Complete! Imported '.$importedItemCount.' item(s).');
                $this->totalItemCount = $importedItemCount;

            } else {

                // LIVE data load. Need to do it in stages.
                $this->warn('Loading LIVE data from eShop. Do not abuse!');
                $this->totalItemCount = 0;
                $this->loadLiveData($eshopLoader);

            }

            $channel = env('SLACK_ALERT_CHANNEL', '');
            if ($channel) {
                \Slack::to('#'.$channel)->send('EshopUSImportData: imported '.$this->totalItemCount.' records');
            }

        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
