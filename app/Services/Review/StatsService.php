<?php

namespace App\Services\Review;

use Illuminate\Support\Collection;

class StatsService
{
    public function calculateReviewCount(Collection $reviews)
    {
        return $reviews->count();
    }

    public function calculateReviewAverage(Collection $reviews)
    {
        $reviewCount = $reviews->count();

        if ($reviewCount == 0) return null;

        $sumTotal = 0;

        foreach ($reviews as $review) {
            $reviewScore = $review->rating_normalised;
            $sumTotal += $reviewScore;
        }

        $avgScore = round(($sumTotal / $reviewCount), 1);

        return $avgScore;
    }
}