<?php

namespace App\Services;

use App\Game;
use Illuminate\Support\Collection;

class ReviewStatsService
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

    public function calculateStandardDeviation(Collection $reviews)
    {
        $reviewCount = $reviews->count();

        if ($reviewCount == 0) return null;

        $reviewsArray = [];
        foreach ($reviews as $review) {
            $reviewsArray[] = $review['rating_normalised'];
        }

        $sdValue = $this->calculateSd($reviewsArray);

        return round($sdValue, 4);
    }

    // Function to calculate square of value - mean
    public function calculateSdSquare($x, $mean) {
        return pow($x - $mean, 2);
    }

    // Function to calculate standard deviation (uses sd_square)
    public function calculateSd($array) {
        // square root of sum of squares divided by N-1
        return sqrt(array_sum(array_map(array($this, 'calculateSdSquare'), $array, array_fill(0, count($array), (array_sum($array) / count($array)) ) ) ) / (count($array)-1) );
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