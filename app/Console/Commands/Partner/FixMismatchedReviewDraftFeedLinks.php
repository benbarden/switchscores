<?php

namespace App\Console\Commands\Partner;

use App\Models\PartnerFeedLink;
use App\Models\ReviewDraft;

use App\Domain\GameTitleMatch\MatchRule;

use Illuminate\Console\Command;

/**
 * Repairs review drafts whose feed_link_id points to a feed link on a different site.
 *
 * These were created by the active-feeds import while getActive() returned models whose ->id was
 * the site id, not the feed link id (see ImportActiveFeeds / PartnerFeedLink Repository). The
 * importer stamped that site id onto feed_link_id, so each affected draft points at whichever feed
 * link happens to share that id - a feed link on the wrong site.
 *
 * Scope is deliberately just the mismatched rows: it re-attributes each to the correct feed link on
 * its own site, using the same date-then-title-rule logic as the backfill, and leaves legitimately
 * null drafts for the normal backfill to handle.
 */
class FixMismatchedReviewDraftFeedLinks extends Command
{
    protected $signature = 'PartnerFixMismatchedReviewDraftFeedLinks {--dry-run : Report what would change without writing}';

    protected $description = 'Re-attributes review drafts whose feed_link_id points to a feed link on a different site (the site-id/feed-link-id mix-up).';

    public function handle()
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('Dry run - no changes will be written.');
        }

        // A draft's feed link must belong to the same site as the draft. Anything else is the
        // site-id/feed-link-id mix-up, not a legitimate attribution.
        $mismatched = ReviewDraft::query()
            ->join('partner_feed_links', 'partner_feed_links.id', '=', 'review_drafts.feed_link_id')
            ->whereColumn('review_drafts.site_id', '<>', 'partner_feed_links.site_id')
            ->select('review_drafts.id')
            ->pluck('id');

        if ($mismatched->isEmpty()) {
            $this->info('No mismatched drafts found.');
            return 0;
        }

        $this->warn(sprintf('%d mismatched draft(s) found.', $mismatched->count()));

        // Cache each site's feed links once, so a site with many affected drafts isn't queried
        // per draft.
        $feedLinksBySite = PartnerFeedLink::all()->groupBy('site_id');

        $reassigned = 0;
        $cleared = 0;

        foreach (ReviewDraft::whereIn('id', $mismatched)->get() as $draft) {

            $feedLinks = $feedLinksBySite->get($draft->site_id);

            if (!$feedLinks || $feedLinks->isEmpty()) {
                // The draft's own site has no feed links at all - nothing safe to point at.
                $this->line(sprintf('  Draft %d: site %d has no feed links, clearing: %s',
                    $draft->id, $draft->site_id, $draft->item_title));
                if (!$dryRun) {
                    $draft->feed_link_id = null;
                    $draft->save();
                }
                $cleared++;
                continue;
            }

            $target = $this->resolveFeedLink($draft, $feedLinks);

            if ($target === null) {
                // Could not attribute confidently. Null it so the row is honest and the normal
                // backfill or a human can revisit, rather than leaving the wrong link in place.
                $this->line(sprintf('  Draft %d: could not attribute, clearing: %s',
                    $draft->id, $draft->item_title));
                if (!$dryRun) {
                    $draft->feed_link_id = null;
                    $draft->save();
                }
                $cleared++;
                continue;
            }

            $this->line(sprintf('  Draft %d: feed %d -> %d: %s',
                $draft->id, $draft->feed_link_id, $target, $draft->item_title));

            if (!$dryRun) {
                $draft->feed_link_id = $target;
                $draft->save();
            }
            $reassigned++;
        }

        $this->info(sprintf('Done. %d reassigned, %d cleared.', $reassigned, $cleared));

        return 0;
    }

    /**
     * Picks the correct feed link for a draft from its own site's feed links, mirroring the
     * backfill: a single (candidate) feed is used directly; a multi-feed site is resolved by
     * whichever feed's title rule matches. Returns null when it cannot decide.
     *
     * @return int|null feed link id
     */
    private function resolveFeedLink(ReviewDraft $draft, $feedLinks)
    {
        if (count($feedLinks) == 1) {
            return $feedLinks->first()->id;
        }

        // A draft cannot have come from a feed that did not exist yet.
        $candidateFeeds = $feedLinks->filter(function ($feedLink) use ($draft) {
            return $feedLink->created_at <= $draft->created_at;
        });

        if (count($candidateFeeds) == 1) {
            return $candidateFeeds->first()->id;
        }

        $matchingFeedIds = [];

        foreach ($candidateFeeds as $feedLink) {

            if (!$feedLink->title_match_rule_pattern || is_null($feedLink->title_match_rule_index)) {
                continue;
            }

            $matchRule = new MatchRule(
                $feedLink->title_match_rule_pattern,
                $feedLink->title_match_rule_index
            );

            if ($matchRule->generateMatch($draft->item_title) !== null) {
                $matchingFeedIds[] = $feedLink->id;
            }
        }

        return count($matchingFeedIds) == 1 ? $matchingFeedIds[0] : null;
    }
}
