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
        $this->info('Clearing previous data');

        \DB::statement("TRUNCATE TABLE eshop_europe_games");

        $eshopLoader = new LoaderEurope();

        try {

            //$eshopLoader->loadLocalData('europe-test-1500-games.json');
            $eshopLoader->loadGames();
            $responseArray = $eshopLoader->getResponseData();

            $gameData = $responseArray['response']['docs'];
            if (!is_array($gameData)) {
                throw new \Exception('Cannot load game data');
            }

            $this->info('Importing data...');
            $eshopLoader->importToDb();

        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
