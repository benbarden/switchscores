<?php


namespace App\Services;

use App\ReviewLink;


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

    public function getAll()
    {
        $reviewSites = ReviewLink::orderBy('id', 'desc')->get();
        return $reviewSites;
    }
}