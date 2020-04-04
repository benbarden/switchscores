<?php

namespace App\Console\Commands\DataSource\NintendoCoUk;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\Traits\SwitchServices;

use App\Services\DataSources\NintendoCoUk\Importer;
use App\Services\DataSources\NintendoCoUk\Parser;

class ImportParseLink extends Command
{
    use SwitchServices;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'DSNintendoCoUkImportParseLink {mode?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports and parses data, then links it to games.';

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
        $argMode = $this->argument('mode');

        $logger = Log::channel('cron');
        $logger->info(' *************** '.$this->signature.' *************** ');

        $importer = new Importer();

        $sourceId = $this->getServiceDataSource()->getSourceNintendoCoUk()->id;

        try {

            // PARSE-ONLY MODE
            if ($argMode == 'parse') {

                $logger->info('Parse-only mode; skipping data download from eShop.');

            } else {

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

            }

            // Parsed data
            $logger->info('Clearing previous parsed data...');
            $this->getServiceDataSourceParsed()->deleteBySourceId($sourceId);
            $logger->info('Parsing data...');
            $rawSourceData = $this->getServiceDataSourceRaw()->getBySourceId($sourceId);
            foreach ($rawSourceData as $rawItem) {
                $parser = new Parser($rawItem);
                $parsedItem = $parser->parseItem();
                $parsedItem->save();
            }
            $logger->info('Parsing complete');

            // Link games
            $logger->info('Updating game links...');
            $this->getServiceDataSourceParsed()->updateGameIds();
            $logger->info('Linking complete');

        } catch (\Exception $e) {
            $logger->error($e->getMessage());
        }
    }
}
