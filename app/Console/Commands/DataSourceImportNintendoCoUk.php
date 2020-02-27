<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\Traits\SwitchServices;

use App\Services\DataSources\NintendoCoUk\Importer;
use App\Services\DataSources\NintendoCoUk\Parser;

class DataSourceImportNintendoCoUk extends Command
{
    use SwitchServices;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'DataSourceImportNintendoCoUk';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Data source importer.';

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

        $importer = new Importer();
        $parser = new Parser();

        $sourceId = $this->getServiceDataSource()->getSourceNintendoCoUk()->id;

        try {

            if (\App::environment() == 'localx') {
                $logger->info('Loading local data from JSON file');
                $importer->loadLocalData('europe-test-1500-games.json');
            } else {
                $logger->warn('Loading LIVE data from eShop. Do not abuse!');
                $importer->loadGames();
            }
            $responseArray = $importer->getResponseData();

            $gameData = $responseArray['response']['docs'];
            if (!is_array($gameData)) {
                throw new \Exception('Cannot load game data');
            }
            $logger->info('Successfully loaded game data into temporary storage.');

            // Raw data
            $logger->info('Clearing previous raw data...');
            $this->getServiceDataSourceRaw()->deleteBySourceId($sourceId);
            $logger->info('Importing raw data...');
            $importer->importToDb($sourceId);
            $importedItemCount = $importer->getImportedCount();
            $logger->info('Imported '.$importedItemCount.' item(s)');

            // Parsed data
            $logger->info('Clearing previous parsed data...');
            $this->getServiceDataSourceParsed()->deleteBySourceId($sourceId);
            $logger->info('Parsing data...');
            $rawSourceData = $this->getServiceDataSourceRaw()->getBySourceId($sourceId);
            foreach ($rawSourceData as $rawItem) {
                $parsedItem = $parser->parseItem($rawItem);
                $parsedItem->save();
            }
            $logger->info('Parsing complete');

        } catch (\Exception $e) {
            $logger->error($e->getMessage());
        }
    }
}
