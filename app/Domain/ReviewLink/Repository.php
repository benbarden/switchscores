<?php


namespace App\Domain\ReviewLink;

use App\Models\ReviewLink;
use App\Models\ReviewSite;

class Repository
{
    public function create(
        $gameId, $siteId, $url, $ratingOriginal, $ratingNormalised, $reviewDate,
        $reviewType = ReviewLink::TYPE_IMPORTED, $desc = null
    )
    {
        return ReviewLink::create([
            'game_id' => $gameId,
            'site_id' => $siteId,
            'url' => $url,
            'rating_original' => $ratingOriginal,
            'rating_normalised' => $ratingNormalised,
            'review_date' => $reviewDate,
            'review_type' => $reviewType,
            'description' => $desc,
        ]);
    }

    public function find($linkId)
    {
        return ReviewLink::find($linkId);
    }

    public function byGame($gameId)
    {
        return ReviewLink::where('game_id', $gameId)->get();
    }

    public function byGameAndSite($gameId, $siteId)
    {
        return ReviewLink::where('game_id', $gameId)->where('site_id', $siteId)->first();
    }

    public function getNormalisedRating($ratingOriginal, $ratingScale)
    {
        $normalisedScaleLimit = 10;

        if ($ratingScale != $normalisedScaleLimit) {
            $scaleMultiple = $normalisedScaleLimit / $ratingScale;
            $ratingNormalised = round($ratingOriginal * $scaleMultiple, 2);
        } else {
            $ratingNormalised = $ratingOriginal;
        }

        return $ratingNormalised;
    }

    public function getLatestBySite($siteId)
    {
        return ReviewLink::where('site_id', $siteId)
            ->orderBy('review_date', 'desc')
            ->orderBy('id', 'desc')
            ->first();
    }
}