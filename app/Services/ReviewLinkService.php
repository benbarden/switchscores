<?php


namespace App\Services;

use App\ReviewLink;
use App\ReviewSite;
use App\Partner;

class ReviewLinkService
{
    /**
     * @param $gameId
     * @param $siteId
     * @param $url
     * @param $ratingOriginal
     * @param $ratingNormalised
     * @param $reviewDate
     * @param $reviewType
     * @param null $userId
     * @return ReviewLink
     */
    public function create(
        $gameId, $siteId, $url, $ratingOriginal, $ratingNormalised, $reviewDate, $reviewType, $userId = null
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
            'user_id' => $userId,
        ]);
    }

    /**
     * @param ReviewLink $reviewLinkData
     * @param $gameId
     * @param $siteId
     * @param $url
     * @param $ratingOriginal
     * @param $ratingNormalised
     * @param $reviewDate
     * @return void
     */
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

    /**
     * @param $id
     * @return ReviewLink
     */
    public function find($id)
    {
        return ReviewLink::find($id);
    }

    public function getAll($limit = null)
    {
        $reviewLinks = ReviewLink::orderBy('id', 'desc');
        if ($limit) {
            $reviewLinks = $reviewLinks->limit($limit);
        }
        $reviewLinks = $reviewLinks->get();
        return $reviewLinks;
    }

    public function getAllBySite($siteId)
    {
        $reviewLinks = ReviewLink::where('site_id', $siteId)
            ->orderBy('review_date', 'desc')
            ->orderBy('id', 'desc')
            ->get();
        return $reviewLinks;
    }

    public function getAllGameIdsReviewedBySite($siteId)
    {
        $gameIds = ReviewLink::select('review_links.game_id')
            ->where('site_id', $siteId);
        return $gameIds;
    }

    public function countAllGameIdsReviewedBySite($siteId)
    {
        $gameIds = ReviewLink::select('review_links.game_id')
            ->where('site_id', $siteId)->count();
        return $gameIds;
    }

    public function countBySite($siteId)
    {
        return ReviewLink::where('site_id', $siteId)->count();
    }

    public function countActive()
    {
        $listReviews = ReviewLink::select('review_links.*', 'review_sites.name')
            ->join('partners', 'review_links.site_id', '=', 'partners.id')
            ->where('partners.status', '=', Partner::STATUS_ACTIVE)
            ->count();
        return $listReviews;
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
        $gameReviews = ReviewLink::select('review_links.*', 'partners.name')
            ->join('partners', 'review_links.site_id', '=', 'partners.id')
            ->where('game_id', $gameId)
            ->where('partners.status', '=', Partner::STATUS_ACTIVE)
            ->orderBy('review_links.review_date', 'desc')
            ->orderBy('partners.name', 'asc')
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

    public function getByUser($userId)
    {
        $reviewLinks = ReviewLink::where('user_id', $userId)
            ->orderBy('review_date', 'desc')
            ->orderBy('id', 'desc')
            ->get();
        return $reviewLinks;
    }

    public function getNormalisedRating($ratingOriginal, Partner $reviewSite)
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
            LEFT JOIN partners p ON rl.site_id = p.id
            WHERE p.id = ?
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