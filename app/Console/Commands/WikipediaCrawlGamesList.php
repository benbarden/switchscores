<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Services\HtmlLoader\Wikipedia\Crawler as WikiCrawler;

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
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    private function processTableData($tableData)
    {
        $counter = -1;

        foreach ($tableData as $row) {

            $counter++;

            // Skip the first two rows
            if (in_array($counter, [0, 1])) continue;

            // Handle fields
            if (strlen($row[0]) > 150) {
                $rowTitle = substr($row[0], 0, 150);
            } else {
                $rowTitle = $row[0];
            }

            if (strlen($row[1]) > 150) {
                $rowGenres = substr($row[1], 0, 150);
            } else {
                $rowGenres = $row[1];
            }

            $rowDevs = $row[2];
            $rowPubs = $row[3];

            if (strlen($row[4]) > 20) {
                $rowExcls = substr($row[4], 0, 20);
            } else {
                $rowExcls = $row[4];
            }

            if (is_array($row[5])) {
                if (count($row[5]) == 2) {
                    $rowRelDateJP = $row[5][1];
                } else {
                    $this->warn('Unexpected array count for game '.$rowTitle);
                    $rowRelDateJP = $row[5];
                }
            } else {
                $rowRelDateJP = $row[5];
            }

            if (is_array($row[6])) {
                if (count($row[6]) == 2) {
                    $rowRelDateNA = $row[6][1];
                } else {
                    $rowRelDateNA = $row[6][0];
                }
            } else {
                $rowRelDateNA = $row[6];
            }

            if (is_array($row[7])) {
                if (count($row[7]) == 2) {
                    $rowRelDatePAL = $row[7][1];
                } else {
                    $rowRelDatePAL = $row[7][0];
                }
            } else {
                $rowRelDatePAL = $row[7];
            }

            $this->info('Processing item: '.$rowTitle);

            \DB::insert('
                INSERT INTO crawler_wikipedia_games_list_source(title, genres, developers, publishers,
                exclusive, release_date_jp, release_date_na, release_date_pal, created_at, updated_at)
                VALUES(?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ', [$rowTitle, $rowGenres, $rowDevs, $rowPubs, $rowExcls, $rowRelDateJP, $rowRelDateNA, $rowRelDatePAL]);

        }
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

        // Page 1
        $wikiCrawler->crawlPageOne();
        $wikiCrawler->extractRows();
        $tableData = $wikiCrawler->getTableData();
        $this->processTableData($tableData);

        // Page 2
        $wikiCrawler->crawlPageTwo();
        $wikiCrawler->extractRows();
        $tableData = $wikiCrawler->getTableData();
        $this->processTableData($tableData);
    }
}
