<?php

namespace App\Services;

use App\Game;
use Illuminate\Support\Collection;

class ReviewStatsService
{
    public function calculateReviewCount(Collection $reviewLinks, Collection $quickReviews = null)
    {

        $reviewLinkCount = $reviewLinks->count();
        $quickReviewCount = $quickReviews->count();
        $totalReviewCount = $reviewLinkCount + $quickReviewCount;
        return $totalReviewCount;
    }

    public function calculateReviewAverage(Collection $reviewLinks, Collection $quickReviews = null)
    {
        $reviewCount = $reviewLinks->count() + $quickReviews->count();

        if ($reviewCount == 0) return null;

        $sumTotal = 0;

        foreach ($reviewLinks as $review) {
            $reviewScore = $review->rating_normalised;
            $sumTotal += $reviewScore;
        }

        foreach ($quickReviews as $review) {
            $reviewScore = $review->review_score;
            $sumTotal += $reviewScore;
        }

        $avgScore = round(($sumTotal / $reviewCount), 2);
        $avgScore = number_format($avgScore, 2);

        return $avgScore;
    }

    public function calculateStandardDeviation(Collection $reviewLinks, Collection $quickReviews = null)
    {
        $reviewCount = $reviewLinks->count() + $quickReviews->count();

        if ($reviewCount == 0) return null;

        $reviewsArray = [];
        foreach ($reviewLinks as $review) {
            $reviewsArray[] = $review['rating_normalised'];
        }
        foreach ($quickReviews as $review) {
            $reviewsArray[] = $review['review_score'];
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

    public function updateGameReviewStats(Game $game, $reviewLinks, $quickReviews)
    {
        $reviewCount = $this->calculateReviewCount($reviewLinks, $quickReviews);
        $reviewAverage = $this->calculateReviewAverage($reviewLinks, $quickReviews);

        $game->review_count = $reviewCount;
        $game->rating_avg = $reviewAverage;
        $game->save();
    }

    public function calculateContributionPercentage($contribTotal, $siteTotal)
    {
        if ($siteTotal == 0) return 0;
        if ($contribTotal == 0) return 0;

        $percentage = ($contribTotal / $siteTotal) * 100;
        $percentage = round($percentage, 2);

        return $percentage;
    }
}