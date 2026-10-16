<?php

namespace App\Domain\PartnerFeedLink;

use App\Models\PartnerFeedLinkRun;

/**
 * Run history for feed links: written by the importer, read by the staff runs page.
 */
class RunRepository
{
    /**
     * Runs kept per feed link (Ben, 2026-10-16). Pruned on every write, so the table never
     * holds more than this per feed.
     */
    const KEEP_RUNS = 30;

    public function record(int $feedLinkId, array $values): PartnerFeedLinkRun
    {
        $run = PartnerFeedLinkRun::create(array_merge($values, ['feed_link_id' => $feedLinkId]));

        $this->prune($feedLinkId);

        return $run;
    }

    public function prune(int $feedLinkId): void
    {
        $keepIds = PartnerFeedLinkRun::where('feed_link_id', $feedLinkId)
            ->orderBy('run_at', 'desc')
            ->orderBy('id', 'desc')
            ->limit(self::KEEP_RUNS)
            ->pluck('id');

        PartnerFeedLinkRun::where('feed_link_id', $feedLinkId)
            ->whereNotIn('id', $keepIds)
            ->delete();
    }

    public function getForFeedLink(int $feedLinkId)
    {
        return PartnerFeedLinkRun::where('feed_link_id', $feedLinkId)
            ->orderBy('run_at', 'desc')
            ->orderBy('id', 'desc')
            ->get();
    }
}
