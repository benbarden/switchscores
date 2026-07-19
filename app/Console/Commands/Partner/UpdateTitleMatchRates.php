<?php

namespace App\Console\Commands\Partner;

use App\Domain\PartnerFeedLink\Repository as PartnerFeedLinkRepository;
use App\Domain\PartnerFeedLink\TitleMatchRate;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateTitleMatchRates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'PartnerUpdateTitleMatchRates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculates the stored title match rate for every live feed link, so the feed links table stays current.';

    public function handle()
    {
        $logger = Log::channel('cron');

        $logger->info(' *************** '.$this->signature.' *************** ');

        $repoPartnerFeedLink = new PartnerFeedLinkRepository();
        $titleMatchRate = new TitleMatchRate();

        // Live feeds only - archived and broken ones are noise on the table.
        $feedLinks = $repoPartnerFeedLink->getActive();

        foreach ($feedLinks as $feedLink) {

            // getActive() joins review_sites, so re-read the feed link on its own to avoid
            // saving a model built from a joined row.
            $partnerFeedLink = $repoPartnerFeedLink->find($feedLink->feed_link_id);
            if (!$partnerFeedLink) continue;

            $rate = $titleMatchRate->update($partnerFeedLink);

            if ($rate === null) {
                // These are different problems and must not be reported as one: a feed with no
                // rule may be matching fine at import time on raw titles, whereas a feed with
                // no drafts is not importing at all.
                $reason = !$partnerFeedLink->title_match_rule_pattern
                    ? 'no match rule set (may be matching on raw titles at import)'
                    : 'no drafts to sample';
                $logger->info(sprintf('Feed %s (%s): %s', $partnerFeedLink->id, $feedLink->name, $reason));
            } else {
                $logger->info(sprintf('Feed %s (%s): %s%%', $partnerFeedLink->id, $feedLink->name, $rate));
            }
        }

        return 0;
    }
}
