<?php

namespace App\Console\Commands;

use App\Domain\AffiliateCodes\CreatorsApi;
use App\Domain\BuyersGuide\OfferRefresher;
use App\Domain\JobRun\CronRunContext;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Hourly: fetches Amazon US prices and images for the buyer's guide, for its Deals section and
 * hardware images. Run from its own line in scripts/switchscores-cron.
 */
class BuyersGuideRefreshOffers extends Command
{
    protected $signature = 'buyers-guide:refresh-offers';

    protected $description = 'Fetch Amazon US prices and images for the buyer\'s guide.';

    public function handle(OfferRefresher $refresher, CreatorsApi $api): int
    {
        $logger = Log::channel('cron');

        if (!$api->isConfigured()) {
            $this->warn('The Creators API credentials are not set; nothing fetched.');
            CronRunContext::warn('Buyer\'s guide offers: the Creators API credentials are not set.');
            return self::SUCCESS;
        }

        $result = $refresher->refresh();

        $summary = 'Buyer\'s guide offers: '.$result['returned'].' of '.$result['asked'].' ASINs returned';
        $this->info($summary);
        $logger->info($summary);

        foreach ($result['errors'] as $error) {
            $this->warn($error);
            $logger->warning('Buyer\'s guide offers: '.$error);
        }

        if ($result['errors']) {
            CronRunContext::warn('Buyer\'s guide offers: '.count($result['errors']).' batch(es) failed, e.g. '.$result['errors'][0]);
        }

        if ($result['asked'] > 0 && $result['returned'] === 0) {
            // Nothing saved; the earlier offers expire on their own and the page drops the deals
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
