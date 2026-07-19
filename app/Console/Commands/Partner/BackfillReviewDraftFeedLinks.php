<?php

namespace App\Console\Commands\Partner;

use App\Models\PartnerFeedLink;
use App\Models\ReviewDraft;

use App\Domain\GameTitleMatch\MatchRule;

use Illuminate\Console\Command;

class BackfillReviewDraftFeedLinks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'PartnerBackfillReviewDraftFeedLinks {--dry-run : Report what would change without writing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sets feed_link_id on existing feed review drafts. Sites with one feed are assigned directly; multi-feed sites are attributed by testing the draft title against each feed match rule.';

    public function handle()
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('Dry run - no changes will be written.');
        }

        $feedLinksBySite = PartnerFeedLink::all()->groupBy('site_id');

        $totalAssigned = 0;
        $totalSkipped = 0;

        foreach ($feedLinksBySite as $siteId => $feedLinks) {

            $drafts = ReviewDraft::where('site_id', $siteId)
                ->where('source', ReviewDraft::SOURCE_FEED)
                ->whereNull('feed_link_id')
                ->get();

            if ($drafts->isEmpty()) {
                continue;
            }

            if (count($feedLinks) == 1) {

                // Unambiguous: every feed draft for this site came from its only feed.
                $feedLinkId = $feedLinks[0]->id;
                $count = $drafts->count();

                if (!$dryRun) {
                    ReviewDraft::where('site_id', $siteId)
                        ->where('source', ReviewDraft::SOURCE_FEED)
                        ->whereNull('feed_link_id')
                        ->update(['feed_link_id' => $feedLinkId]);
                }

                $this->line(sprintf('Site %s: %d draft(s) -> feed %d (single feed)', $siteId, $count, $feedLinkId));
                $totalAssigned += $count;

                continue;
            }

            // Multi-feed site: attribute each draft by which feed's title rule matches.
            // A draft matching none, or more than one, is left null rather than guessed.
            $assigned = 0;
            $skipped = 0;

            foreach ($drafts as $draft) {

                // A draft cannot have come from a feed that didn't exist yet. This alone
                // resolves most older drafts on sites where a second feed was added later.
                $candidateFeeds = $feedLinks->filter(function ($feedLink) use ($draft) {
                    return $feedLink->created_at <= $draft->created_at;
                });

                if (count($candidateFeeds) == 1) {
                    if (!$dryRun) {
                        $draft->feed_link_id = $candidateFeeds->first()->id;
                        $draft->save();
                    }
                    $assigned++;
                    continue;
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

                if (count($matchingFeedIds) == 1) {
                    if (!$dryRun) {
                        $draft->feed_link_id = $matchingFeedIds[0];
                        $draft->save();
                    }
                    $assigned++;
                } else {
                    $reason = count($matchingFeedIds) == 0 ? 'no rule matched' : 'matched multiple feeds';
                    $this->line(sprintf('  Skipped draft %d (%s): %s', $draft->id, $reason, $draft->item_title));
                    $skipped++;
                }
            }

            $this->line(sprintf(
                'Site %s: %d assigned, %d skipped (%d feeds, attributed by title rule)',
                $siteId, $assigned, $skipped, count($feedLinks)
            ));

            $totalAssigned += $assigned;
            $totalSkipped += $skipped;
        }

        $this->info(sprintf('Done. %d draft(s) assigned, %d skipped.', $totalAssigned, $totalSkipped));

        return 0;
    }
}
