<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Services\Eshop\LoaderEurope;

class EshopEuropeImportData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'EshopEuropeImportData';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports data from the European eShop.';

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

        $this->info('Clearing previous data');

        \DB::statement("TRUNCATE TABLE eshop_europe_games");

        $eshopLoader = new LoaderEurope();

        try {

            if (\App::environment() == 'localx') {
                $this->info('Loading local data from JSON file');
                $eshopLoader->loadLocalData('europe-test-1500-games.json');
            } else {
                $this->warn('Loading LIVE data from eShop. Do not abuse!');
                $eshopLoader->loadGames();
            }
            $responseArray = $eshopLoader->getResponseData();

            $gameData = $responseArray['response']['docs'];
            if (!is_array($gameData)) {
                throw new \Exception('Cannot load game data');
            }

            $this->info('Importing data...');
            $eshopLoader->importToDb();
            $importedItemCount = $eshopLoader->getImportedCount();
            $this->info('Complete! Imported '.$importedItemCount.' item(s).');

            $channel = env('SLACK_ALERT_CHANNEL', '');
            if ($channel) {
                \Slack::to('#'.$channel)->send('EshopEuropeImportData: imported '.$importedItemCount.' records');
            }

        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
