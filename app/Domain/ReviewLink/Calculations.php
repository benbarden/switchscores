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
}