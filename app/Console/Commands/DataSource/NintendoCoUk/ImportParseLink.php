<?php

namespace App\Console\Commands\DataSource\NintendoCoUk;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\Domain\DataSource\Repository as DataSourceRepository;
use App\Domain\DataSourceRaw\Repository as DataSourceRawRepository;
use App\Domain\DataSourceParsed\Repository as DataSourceParsedRepository;

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
    public function __construct(
    )
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

        $repoDataSource = new DataSourceRepository();
        $repoDataSourceRaw = new DataSourceRawRepository();
        $repoDataSourceParsed = new DataSourceParsedRepository();

        $sourceId = $repoDataSource->getSourceNintendoCoUk()->id;

        try {

            // PARSE-ONLY MODE
            if ($argMode == 'parse') {

                $logger->info('Parse-only mode; skipping data download from eShop.');

            } else {

                // Do cleanup first
                $logger->info('Clearing previous raw data...');
                $repoDataSourceRaw->deleteBySourceId($sourceId);
                $logger->info('Clearing previous parsed data...');
                $repoDataSourceParsed->deleteBySourceId($sourceId);

                //
                //if (\App::environment() == 'localx') {
                //    $logger->info('Loading local data from JSON file');
                //    $importer->loadLocalData('europe-test-1500-games.json');
                //} else {
                //}
                $logger->warning('Loading LIVE data from eShop. Do not abuse!');

                $loadLimit = 1000;
                $maxExpectedItems = 10000;
                $loadOffsets = [0];
                for ($i = $loadLimit; $i <= $maxExpectedItems; $i+=$loadLimit) {
                    $loadOffsets[] = $i;
                }

                // Switch 1
                foreach ($loadOffsets as $offset) {

                    $logger->info(sprintf('Loading %s items; offset %s', $loadLimit, $offset));
                    $importer->loadGames($loadLimit, $offset);

                    $responseArray = $importer->getResponseData();

                    $gameData = $responseArray['response']['docs'];
                    if (!is_array($gameData)) {
                        $logger->error('Cannot load game data');
                        continue;
                    }
                    $logger->info('Successfully loaded game data into temporary storage.');

                    // Raw data
                    $logger->info('Importing raw data...');
                    $importer->importToDb($sourceId);
                    $importedItemCount = $importer->getImportedCount();
                    $logger->info('Imported '.$importedItemCount.' item(s)');

                }

                // Switch 2
                $logger->info('Loading Switch 2 data... ');
                $importer->loadGamesSwitch2($loadLimit, 0);

                $responseArray = $importer->getResponseData();

                $gameData = $responseArray['response']['docs'];
                if (!is_array($gameData)) {
                    $logger->error('Cannot load game data');
                } else {
                    $logger->info('Successfully loaded game data into temporary storage.');

                    // Raw data
                    $logger->info('Importing raw data...');
                    $importer->importToDb($sourceId);
                    $importedItemCount = $importer->getImportedCount();
                    $logger->info('Imported '.$importedItemCount.' item(s)');
                }

            }

            // Parsed data
            $logger->info('Parsing data...');
            $parsedItemCount = 0;
            $rawSourceData = $repoDataSourceRaw->getBySourceId($sourceId);
            $totalItemCount = count($rawSourceData);
            foreach ($rawSourceData as $rawItem) {
                if (($parsedItemCount % 1000) == 0) {
                    $logger->info(sprintf('Parsed %s/%s items', $parsedItemCount, $totalItemCount));
                }
                $parser = new Parser($rawItem, $logger);
                $parsedItem = $parser->parseItem();
                $parsedItem->save();
                $parsedItemCount++;
            }
            $logger->info('Parsing complete. Parsed '.$parsedItemCount.' items(s).');

            // Link games
            $logger->info('Updating game links...');
            $repoDataSourceParsed->updateNintendoCoUkGameIds();
            $logger->info('Linking complete');

        } catch (\Exception $e) {
            $logger->error($e->getMessage());
        }
    }
}
