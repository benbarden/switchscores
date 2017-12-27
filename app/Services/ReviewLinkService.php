<?php


namespace App\Services;

use App\ReviewLink;
use App\ReviewSite;

class ReviewLinkService
{
    public function create(
        $gameId, $siteId, $url, $ratingOriginal, $ratingNormalised, $reviewDate, $reviewType
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

    public function getLatestBySite($siteId, $limit = 20)
    {
        $reviewLinks = ReviewLink::where('site_id', $siteId)
            ->orderBy('review_date', 'desc')
            ->orderBy('id', 'desc')
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

    public function getByGameAndSite($gameId, $siteId)
    {
        $gameReview = ReviewLink::where('game_id', $gameId)
            ->where('site_id', $siteId)
            ->first();
        return $gameReview;
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

    public function getSiteReviewStats($siteId)
    {
        $reviewAverage = \DB::select("
            SELECT
            count(*) AS ReviewCount,
            sum(rl.rating_normalised) AS ReviewSum,
            avg(rl.rating_normalised) AS ReviewAvg
            FROM review_links rl
            LEFT JOIN review_sites rs ON rl.site_id = rs.id
            WHERE rs.id = ?
        ", array($siteId));

        return $reviewAverage;
    }

    public function getSiteScoreDistribution($siteId)
    {
        $reviewScores = \DB::select("
            SELECT round(rating_normalised, 0) AS RatingValue, count(*) AS RatingCount
            FROM review_links
            WHERE site_id = ?
            GROUP BY round(rating_normalised, 0);
        ", array($siteId));

        if (!$reviewScores) return null;

        $scoresArray = [
            '1' => '0',
            '2' => '0',
            '3' => '0',
            '4' => '0',
            '5' => '0',
            '6' => '0',
            '7' => '0',
            '8' => '0',
            '9' => '0',
            '10' => '0',
        ];

        foreach ($reviewScores as $score) {
            $scoreValue = $score->RatingValue;
            $scoreCount = $score->RatingCount;
            $scoresArray[$scoreValue] = $scoreCount;
        }

        return $scoresArray;
    }
}