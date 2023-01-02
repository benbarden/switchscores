<?php

namespace App\Console\Commands\Review;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\Domain\ReviewSite\Repository as ReviewSiteRepository;
use App\Domain\Scraper\ReviewTable as ScraperReviewTable;
use App\Domain\ReviewDraft\ImportScraper;

use App\Traits\SwitchServices;

class ImportByScraper extends Command
{
    use SwitchServices;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ReviewImportByScraper {siteId?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Custom scraper to import reviews from an HTML list.';

    private $repoReviewSite;

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
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle()
    {
        $this->repoReviewSite = new ReviewSiteRepository();

        $argSiteId = $this->argument('siteId');

        $logger = Log::channel('cron');

        $logger->info(' *************** '.$this->signature.' *************** ');

        if ($argSiteId) {

            $reviewSite = $this->repoReviewSite->find($argSiteId);

            if (!$reviewSite) {
                $logger->error('Cannot find review site with id: '.$argSiteId);
                return 0;
            }

            $reviewSiteList = [$reviewSite];

        } else {

            $reviewSiteList = $this->repoReviewSite->getActiveScraper();

        }

        foreach ($reviewSiteList as $reviewSite) {

            $scraper = new ScraperReviewTable;

            if ($reviewSite->name == 'Nintendo World Report') {

                $scraper->crawlPage('https://www.nintendoworldreport.com/review/');
                $scraper->extractRows('results');
                $tableData = $scraper->getTableData();

                if (count($tableData) == 0) {
                    $logger->error('Error - no rows returned');
                    return 0;
                }

                foreach ($tableData as $item) {
                    try {
                        $importScraper = new ImportScraper;
                        $importScraper->processItemNWR($item, $reviewSite);
                    } catch (\Exception $e) {
                        $logger->error($e->getMessage());
                    }
                }

            } elseif ($reviewSite->name == 'Pocket Tactics') {

                $reviewYear = '2023';
                $scraper->crawlPage('https://www.pockettactics.com/best-mobile-games-'.$reviewYear);
                $scraper->extractRows('review-data');
                $tableData = $scraper->getTableData();

                if (count($tableData) == 0) {
                    $logger->error('Error - no rows returned');
                    return 0;
                }

                foreach ($tableData as $item) {
                    try {
                        $importScraper = new ImportScraper;
                        $importScraper->processItemPocketTactics($item, $reviewSite, $reviewYear);
                    } catch (\Exception $e) {
                        $logger->error($e->getMessage());
                    }
                }

            } else {

                $logger->error('Scraper support is not provided for this review site');
                return 0;

            }

        }
    }
}
