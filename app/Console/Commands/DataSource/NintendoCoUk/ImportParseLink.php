<?php

namespace App\Console\Commands\DataSource\NintendoCoUk;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\Domain\DataSource\Repository as DataSourceRepository;
use App\Domain\DataSourceRaw\Repository as DataSourceRawRepository;
use App\Domain\DataSourceParsed\Repository as DataSourceParsedRepository;

use App\Services\DataSources\NintendoCoUk\Importer;
use App\Services\DataSources\NintendoCoUk\Parser;

class ImportParseLink extends Command
{
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
        private DataSourceRepository $repoDataSource,
        private DataSourceRawRepository $repoDataSourceRaw,
        private DataSourceParsedRepository $repoDataSourceParsed,
        private Importer $importer,
        private Parser $parser
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

        $sourceId = $this->repoDataSource->getSourceNintendoCoUk()->id;

        try {

            // PARSE-ONLY MODE
            if ($argMode == 'parse') {

                $logger->info('Parse-only mode; skipping data download from eShop.');

            } else {

                // Do cleanup first
                $logger->info('Clearing previous raw data...');
                $this->repoDataSourceRaw->deleteBySourceId($sourceId);
                $logger->info('Clearing previous parsed data...');
                $this->repoDataSourceParsed->deleteBySourceId($sourceId);

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
                    $this->importer->loadGames($loadLimit, $offset);

                    $responseArray = $this->importer->getResponseData();

                    $gameData = $responseArray['response']['docs'];
                    if (!is_array($gameData)) {
                        $logger->error('Cannot load game data');
                        continue;
                    }
                    $logger->info('Successfully loaded game data into temporary storage.');

                    // Raw data
                    $logger->info('Importing raw data...');
                    $this->importer->importToDb($sourceId);
                    $importedItemCount = $this->importer->getImportedCount();
                    $logger->info('Imported '.$importedItemCount.' item(s)');

                }

                // Switch 2
                $logger->info('Loading Switch 2 data... ');
                $this->importer->loadGamesSwitch2($loadLimit, 0);

                $responseArray = $this->importer->getResponseData();

                $gameData = $responseArray['response']['docs'];
                if (!is_array($gameData)) {
                    $logger->error('Cannot load game data');
                } else {
                    $logger->info('Successfully loaded game data into temporary storage.');

                    // Raw data
                    $logger->info('Importing raw data...');
                    $this->importer->importToDb($sourceId);
                    $importedItemCount = $this->importer->getImportedCount();
                    $logger->info('Imported '.$importedItemCount.' item(s)');
                }

            }

            // Parsed data
            $logger->info('Parsing data...');
            $this->parser->setLogger($logger);
            $parsedItemCount = 0;
            $rawSourceData = $this->repoDataSourceRaw->getBySourceId($sourceId);
            $totalItemCount = count($rawSourceData);
            foreach ($rawSourceData as $rawItem) {
                if (($parsedItemCount % 1000) == 0) {
                    $logger->info(sprintf('Parsed %s/%s items', $parsedItemCount, $totalItemCount));
                }
                $this->parser->setDataSourceRaw($rawItem);
                $parsedItem = $this->parser->parseItem();
                $parsedItem->save();
                $parsedItemCount++;
            }
            $logger->info('Parsing complete. Parsed '.$parsedItemCount.' items(s).');

            // Link games
            $logger->info('Updating game links...');
            $this->repoDataSourceParsed->updateNintendoCoUkGameIds();
            $logger->info('Linking complete');

        } catch (\Exception $e) {
            $logger->error($e->getMessage());
        }
    }
}
