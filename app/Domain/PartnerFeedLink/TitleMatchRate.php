<?php

namespace App\Domain\PartnerFeedLink;

use App\Models\PartnerFeedLink;

use App\Domain\ReviewDraft\Repository as ReviewDraftRepository;

/**
 * Calculates and stores the title match rate for a feed link.
 *
 * Note what this does and does not tell you: the rate is derived from drafts, so a feed that
 * stops loading altogether does not fall to 0% - it simply stops changing. This measures
 * whether the match rule still fits the titles, not whether the feed is alive.
 */
class TitleMatchRate
{
    const SAMPLE_SIZE = 30;

    private $repoReviewDraft;

    public function __construct()
    {
        $this->repoReviewDraft = new ReviewDraftRepository();
    }

    /**
     * @return int|null Percentage, or null when there is nothing meaningful to measure.
     */
    public function calculate(PartnerFeedLink $partnerFeedLink)
    {
        if (!$partnerFeedLink->title_match_rule_pattern
            || is_null($partnerFeedLink->title_match_rule_index)) {
            return null;
        }

        $drafts = $this->repoReviewDraft->getRecentByFeedLink($partnerFeedLink->id, self::SAMPLE_SIZE);

        if ($drafts->isEmpty()) {
            return null;
        }

        $testTitleRule = new TestTitleRule();
        $testTitleRule->setRule(
            $partnerFeedLink->title_match_rule_pattern,
            $partnerFeedLink->title_match_rule_index
        );

        if (!$testTitleRule->validatePattern()['valid']) {
            // An uncompilable rule matches nothing, which is a real 0 rather than "unknown".
            return 0;
        }

        $titles = [];
        foreach ($drafts as $draft) {
            $titles[] = $draft->item_title;
        }

        return $testTitleRule->test($titles)['match_rate'];
    }

    public function update(PartnerFeedLink $partnerFeedLink)
    {
        $rate = $this->calculate($partnerFeedLink);

        $partnerFeedLink->title_match_rate = $rate;
        $partnerFeedLink->title_match_rate_at = $rate === null ? null : now();
        $partnerFeedLink->save();

        return $rate;
    }
}
