<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\Services\ServiceContainer;

class PartnerUpdateFields extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'PartnerUpdateFields {siteId?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates partner fields.';

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
        $argSiteId = $this->argument('siteId');

        $logger = Log::channel('cron');

        $logger->info(' *************** '.$this->signature.' *************** ');

        $serviceContainer = new ServiceContainer();

        $servicePartner = $serviceContainer->getPartnerService();
        $serviceReviewLink = $serviceContainer->getReviewLinkService();

        $reviewSites = $servicePartner->getAllReviewSites();

        if (!$reviewSites) {
            $logger->info('No review sites found. Aborting.');
            return true;
        }

        foreach ($reviewSites as $reviewSite) {

            $siteId = $reviewSite->id;
            $siteName = $reviewSite->name;

            if ($argSiteId && ($siteId != $argSiteId)) continue;

            $reviewCount = $serviceReviewLink->countBySite($siteId);
            $reviewLatest = $serviceReviewLink->getLatestBySite($siteId, 1);
            if (count($reviewLatest) > 0) {
                $latestReviewDate = $reviewLatest[0]['review_date'];
            } else {
                $latestReviewDate = null;
            }

            $padSiteName = str_pad($siteName, 30);
            $padReviewCount = str_pad($reviewCount, 8);

            $logger->info(sprintf("Site: %s Review count: %s Latest review: %s",
                $padSiteName, $padReviewCount, $latestReviewDate));

            // Update fields
            $reviewSite->review_count = $reviewCount;
            $reviewSite->last_review_date = $latestReviewDate;
            $reviewSite->save();

        }
    }
}
