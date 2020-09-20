<?php

namespace App\Console\Commands\DataSource\Wikipedia;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\Traits\SwitchServices;

use App\Services\DataSources\Wikipedia\Importer;
use App\Services\DataSources\Wikipedia\Parser;

class ImportParseLink extends Command
{
    use SwitchServices;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'DSWikipediaImportParseLink {mode?}';

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
     * @throws \Exception
     * @return mixed
     */
    public function handle()
    {
        $argMode = $this->argument('mode');

        $logger = Log::channel('cron');
        $logger->info(' *************** '.$this->signature.' *************** ');

        $importer = new Importer();

        $sourceId = $this->getServiceDataSource()->getSourceWikipedia()->id;

        // PARSE-ONLY MODE
        if ($argMode == 'parse') {

            $logger->info('Parse-only mode; skipping data download from Wikipedia.');

        } else {

            $logger->info('Clearing previous raw data...');
            $this->getServiceDataSourceRaw()->deleteBySourceId($sourceId);

            $logger->info('Crawling page 1...');
            $importer->crawlPage();

            $logger->info('Extracting row data...');
            $importer->extractRows();

            $logger->info('Importing raw data to db...');
            $importer->importToDb($sourceId);

            $logger->info('Crawling page 2...');
            $importer->crawlPage2();

            $logger->info('Extracting row data...');
            $importer->extractRows();

            $logger->info('Importing raw data to db...');
            $importer->importToDb($sourceId);

            $logger->info('Crawling page 3...');
            $importer->crawlPage3();

            $logger->info('Extracting row data...');
            $importer->extractRows();

            $logger->info('Importing raw data to db...');
            $importer->importToDb($sourceId);

        }

        try {

            $serviceGameTitleHash = $this->getServiceGameTitleHash();

            // Parsed data
            $logger->info('Clearing previous parsed data...');
            $this->getServiceDataSourceParsed()->deleteBySourceId($sourceId);
            $logger->info('Parsing data...');
            $rawSourceData = $this->getServiceDataSourceRaw()->getBySourceId($sourceId);
            foreach ($rawSourceData as $rawItem) {
                $parser = new Parser($rawItem);
                $parser->parseItem();
                $parser->linkToGameId($serviceGameTitleHash);
                if ($parser->isOkToSave()) {
                    $parsedItem = $parser->getParsedItem();
                    $parsedItem->save();
                }
            }
            $logger->info('Parsing complete');

        } catch (\Exception $e) {
            $logger->error($e->getMessage());
        }

    }
}
