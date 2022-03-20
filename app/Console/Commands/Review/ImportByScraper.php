<?php

namespace App\Console\Commands\Review;

use App\Domain\Partner\Repository as PartnerRepository;
use App\Domain\Scraper\ReviewTable as ScraperReviewTable;
use App\Domain\ReviewDraft\ImportScraper;
use App\Traits\SwitchServices;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ImportByScraper extends Command
{
    use SwitchServices;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ReviewImportByScraper {partnerId?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Custom scraper to import reviews from an HTML list.';

    private $repoPartner;

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
        $this->repoPartner = new PartnerRepository;

        $argPartnerId = $this->argument('partnerId');

        $logger = Log::channel('cron');

        $logger->info(' *************** '.$this->signature.' *************** ');

        if ($argPartnerId) {

            $partner = $this->repoPartner->find($argPartnerId);

            if (!$partner) {
                $logger->error('Cannot find partner with id: '.$argPartnerId);
                return 0;
            }

            $partnerList = [$partner];

        } else {

            $partnerList = $this->repoPartner->reviewSitesActiveForScraper();

        }

        foreach ($partnerList as $partner) {

            if (!$partner->isReviewSite()) {
                $logger->error('This command only works with review sites');
                return 0;
            }

            $scraper = new ScraperReviewTable;

            if ($partner->name == 'Nintendo World Report') {

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
                        $importScraper->processItemNWR($item, $partner);
                    } catch (\Exception $e) {
                        $logger->error($e->getMessage());
                    }
                }

            } elseif ($partner->name == 'Pocket Tactics') {

                $scraper->crawlPage('https://www.pockettactics.com/best-mobile-games-2022');
                $scraper->extractRows('review-data');
                $tableData = $scraper->getTableData();

                if (count($tableData) == 0) {
                    $logger->error('Error - no rows returned');
                    return 0;
                }

                foreach ($tableData as $item) {
                    try {
                        $importScraper = new ImportScraper;
                        $importScraper->processItemPocketTactics($item, $partner);
                    } catch (\Exception $e) {
                        $logger->error($e->getMessage());
                    }
                }

            } else {

                $logger->error('Scraper support is not provided for this partner');
                return 0;

            }

        }
    }
}
