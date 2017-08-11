<?php


namespace App\Services;

use App\ReviewLink;
use App\ReviewSite;

class ReviewLinkService
{
    public function create($gameId, $siteId, $url, $ratingOriginal, $ratingNormalised)
    {
        ReviewLink::create([
            'game_id' => $gameId,
            'site_id' => $siteId,
            'url' => $url,
            'rating_original' => $ratingOriginal,
            'rating_normalised' => $ratingNormalised,
        ]);
    }

    public function edit(
        ReviewLink $reviewLinkData,
        $gameId, $siteId, $url, $ratingOriginal, $ratingNormalised
    )
    {
        $values = [
            'game_id' => $gameId,
            'site_id' => $siteId,
            'url' => $url,
            'rating_original' => $ratingOriginal,
            'rating_normalised' => $ratingNormalised,
        ];

        $reviewLinkData->fill($values);
        $reviewLinkData->save();
    }

    public function find($id)
    {
        return ReviewLink::find($id);
    }

    public function getAll()
    {
        $reviewSites = ReviewLink::orderBy('id', 'desc')->get();
        return $reviewSites;
    }

    public function getNormalisedRating($ratingOriginal, ReviewSite $reviewSite)
    {
        $normalisedScaleLimit = 10;

        if ($reviewSite->rating_scale != $normalisedScaleLimit) {
            $scaleMultiple = $normalisedScaleLimit / $reviewSite->rating_scale;
            $ratingNormalised = round($ratingOriginal * $scaleMultiple, 2);
        } else {
            $ratingNormalised = $ratingOriginal;
        }

        return $ratingNormalised;
    }
}