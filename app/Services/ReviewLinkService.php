<?php


namespace App\Services;

use App\ReviewLink;
use App\ReviewSite;

class ReviewLinkService
{
    public function create($gameId, $siteId, $url, $ratingOriginal, $ratingNormalised, $reviewDate)
    {
        return ReviewLink::create([
            'game_id' => $gameId,
            'site_id' => $siteId,
            'url' => $url,
            'rating_original' => $ratingOriginal,
            'rating_normalised' => $ratingNormalised,
            'review_date' => $reviewDate,
        ]);
    }

    public function edit(
        ReviewLink $reviewLinkData,
        $gameId, $siteId, $url, $ratingOriginal, $ratingNormalised, $reviewDate
    )
    {
        $values = [
            'game_id' => $gameId,
            'site_id' => $siteId,
            'url' => $url,
            'rating_original' => $ratingOriginal,
            'rating_normalised' => $ratingNormalised,
            'review_date' => $reviewDate,
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
        $reviewLinks = ReviewLink::orderBy('id', 'desc')->get();
        return $reviewLinks;
    }

    public function getLatestNaturalOrder($limit = 10)
    {
        $reviewLinks = ReviewLink::orderBy('review_date', 'desc')
            ->limit($limit)
            ->get();
        return $reviewLinks;
    }

    public function getAllWithoutDate()
    {
        $reviewLinks = ReviewLink::whereNull('review_date')
            ->orderBy('id', 'desc')
            ->get();
        return $reviewLinks;
    }

    public function getByGame($gameId)
    {
        $gameReviews = ReviewLink::select('review_links.*', 'review_sites.name')
            ->join('review_sites', 'review_links.site_id', '=', 'review_sites.id')
            ->where('game_id', $gameId)
            ->where('review_sites.active', '=', 'Y')
            ->orderBy('review_links.review_date', 'desc')
            ->orderBy('review_sites.name', 'asc')
            ->get();
        return $gameReviews;
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