<?php

namespace App\Services\Review;

use App\Game;
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

        $avgScore = round(($sumTotal / $reviewCount), 2);

        return $avgScore;
    }

    public function updateGameReviewStats(Game $game)
    {
        $gameReviews = $game->reviews()->get();

        $reviewCount = $this->calculateReviewCount($gameReviews);
        $reviewAverage = $this->calculateReviewAverage($gameReviews);

        $game->review_count = $reviewCount;
        $game->rating_avg = $reviewAverage;
        $game->save();
    }
}