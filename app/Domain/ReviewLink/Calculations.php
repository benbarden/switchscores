<?php

namespace App\Domain\ReviewLink;

use App\Models\ReviewLink;

class Calculations
{
    public function normaliseRating($rating, $ratingScale)
    {
        $standardScale = ReviewLink::STANDARD_RATING_SCALE;

        if ($ratingScale != $standardScale) {
            $scaleMultiple = $standardScale / $ratingScale;
            $ratingNormalised = round($rating * $scaleMultiple, 2);
        } else {
            $ratingNormalised = $rating;
        }

        return $ratingNormalised;
    }

    public function contributionPercentage($contribTotal, $siteTotal)
    {
        if ($siteTotal == 0) return 0;
        if ($contribTotal == 0) return 0;

        $percentage = ($contribTotal / $siteTotal) * 100;
        $percentage = round($percentage, 2);

        return $percentage;
    }
}