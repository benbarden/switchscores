<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Services\HtmlLoader\Wikipedia\Crawler as WikiCrawler;
use App\Services\HtmlLoader\Wikipedia\Parser as WikiParser;

class WikipediaCrawlGamesList extends Command
{
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
        $this->info('Clearing previous source data');

        \DB::statement("TRUNCATE TABLE crawler_wikipedia_games_list_source");

        $wikiCrawler = new WikiCrawler();
        $wikiParser = new WikiParser();

        try {

            $this->info('Crawling page...');
            $wikiCrawler->crawlPage();

            $this->info('Extracting row data...');
            $wikiCrawler->extractRows();
            $tableData = $wikiCrawler->getTableData();

            $this->info('Processing table data...');
            $wikiParser->processTableData($tableData);

        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
