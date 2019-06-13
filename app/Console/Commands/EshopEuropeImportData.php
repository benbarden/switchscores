<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

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
        $logger = Log::channel('cron');

        $logger->info(' *************** '.$this->signature.' *************** ');

        $eshopLoader = new LoaderEurope();

        try {

            if (\App::environment() == 'localx') {
                $logger->info('Loading local data from JSON file');
                $eshopLoader->loadLocalData('europe-test-1500-games.json');
            } else {
                $logger->warn('Loading LIVE data from eShop. Do not abuse!');
                $eshopLoader->loadGames();
            }
            $responseArray = $eshopLoader->getResponseData();

            $gameData = $responseArray['response']['docs'];
            if (!is_array($gameData)) {
                throw new \Exception('Cannot load game data');
            }
            $logger->info('Successfully loaded game data into temporary storage.');

            // Only clear previous data after passing the load game data check.
            // We already handle errors for missing fields, so the whole import won't fail
            // when a new field is added to the feed.
            $logger->info('Clearing previous data...');
            \DB::statement("TRUNCATE TABLE eshop_europe_games");

            $logger->info('Importing data...');
            $eshopLoader->importToDb();
            $importedItemCount = $eshopLoader->getImportedCount();
            $logger->info('Complete! Imported '.$importedItemCount.' item(s).');

            $channel = env('SLACK_ALERT_CHANNEL', '');
            if ($channel) {
                \Slack::to('#'.$channel)->send('EshopEuropeImportData: imported '.$importedItemCount.' records');
            }

        } catch (\Exception $e) {
            $logger->error($e->getMessage());
        }
    }
}
