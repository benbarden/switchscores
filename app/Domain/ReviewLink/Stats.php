<?php

namespace App\Domain\ReviewLink;

use App\Models\Game;
use App\Models\QuickReview;
use App\Models\ReviewLink;

use Illuminate\Support\Collection;

class Stats
{
    public function calculateReviewCount(Collection $reviewLinks, Collection $quickReviews = null)
    {
        $reviewLinkCount = 0;
        $quickReviewCount = 0;
        if ($reviewLinks) {
            $reviewLinkCount = $reviewLinks->count();
        }
        if ($quickReviews) {
            $quickReviewCount = $quickReviews->count();
        }
        $totalReviewCount = $reviewLinkCount + $quickReviewCount;
        return $totalReviewCount;
    }

    public function calculateReviewAverage(Collection $reviewLinks, Collection $quickReviews = null)
    {
        $reviewLinkCount = 0;
        $quickReviewCount = 0;
        if ($reviewLinks) {
            $reviewLinkCount = $reviewLinks->count();
        }
        if ($quickReviews) {
            $quickReviewCount = $quickReviews->count();
        }

        $reviewCount = $reviewLinkCount + $quickReviewCount;

        if ($reviewCount == 0) return null;

        $sumTotal = 0;

        if ($reviewLinks) {
            foreach ($reviewLinks as $review) {
                $reviewScore = $review->rating_normalised;
                $sumTotal += $reviewScore;
            }
        }

        if ($quickReviews) {
            foreach ($quickReviews as $review) {
                $reviewScore = $review->review_score;
                $sumTotal += $reviewScore;
            }
        }

        $avgScore = round(($sumTotal / $reviewCount), 2);
        $avgScore = number_format($avgScore, 2);

        return $avgScore;
    }

    public function calculateStats($reviewLinks, $quickReviews)
    {
        $reviewCount = $this->calculateReviewCount($reviewLinks, $quickReviews);
        $reviewAverage = $this->calculateReviewAverage($reviewLinks, $quickReviews);

        return [$reviewCount, $reviewAverage];
    }

    public function updateStats(Game $game)
    {
        $gameReviewLinks = $game->reviews;
        $gameQuickReviews = $game->quickReviews->where('item_status', QuickReview::STATUS_ACTIVE);

        $gameReviewStats = $this->calculateStats($gameReviewLinks, $gameQuickReviews);

        $game->review_count = $gameReviewStats[0];
        $game->rating_avg = $gameReviewStats[1];
        $game->save();
    }

    public function countBySite($siteId)
    {
        return ReviewLink::where('site_id', $siteId)->count();
    }
}