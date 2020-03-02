<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\Services\HtmlLoader\Wikipedia\Crawler as WikiCrawler;
use App\Services\HtmlLoader\Wikipedia\Parser as WikiParser;

use App\Traits\SwitchServices;

class WikipediaCrawlGamesList extends Command
{
    use SwitchServices;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'WikipediaCrawlGamesList';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crawls the games list at Wikipedia and saves it to a database table.';

    /**
     * WikipediaCrawlGamesList constructor.
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

        $logger->info('Clearing previous source data');

        \DB::statement("TRUNCATE TABLE crawler_wikipedia_games_list_source");

        $wikiCrawler = new WikiCrawler();
        $wikiParser = new WikiParser();

        try {

            $logger->info('Crawling page 1...');
            $wikiCrawler->crawlPage();

            $logger->info('Extracting row data...');
            $wikiCrawler->extractRows();
            $tableData = $wikiCrawler->getTableData();

            $logger->info('Processing table data...');
            $wikiParser->processTableData($tableData, $logger);

            $logger->info('Crawling page 2...');
            $wikiCrawler->crawlPage2();

            $logger->info('Extracting row data...');
            $wikiCrawler->extractRows();
            $tableData = $wikiCrawler->getTableData();

            $logger->info('Processing table data...');
            $wikiParser->processTableData($tableData, $logger);

        } catch (\Exception $e) {
            $logger->error($e->getMessage());
        }
    }
}
