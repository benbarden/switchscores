<?php


namespace App\Services;

use App\ReviewLink;
use App\ReviewSite;

class ReviewLinkService
{
    public function create($gameId, $siteId, $url, $ratingOriginal, $ratingNormalised, $reviewDate)
    {
        ReviewLink::create([
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
        $reviewSites = ReviewLink::orderBy('id', 'desc')->get();
        return $reviewSites;
    }

    public function getAllWithoutDate()
    {
        $reviewSites = ReviewLink::whereNull('review_date')
            ->orderBy('id', 'desc')
            ->get();
        return $reviewSites;
    }

    public function getByGame($gameId)
    {
        $gameReviews = ReviewLink::select('review_links.*', 'review_sites.name')
            ->join('review_sites', 'review_links.site_id', '=', 'review_sites.id')
            ->where('game_id', $gameId)
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