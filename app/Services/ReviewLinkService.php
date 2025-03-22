<?php


namespace App\Services;

use App\Models\ReviewLink;
use App\Models\ReviewSite;
use Illuminate\Support\Facades\DB;

class ReviewLinkService
{
    public function countActiveByYearMonth($year, $month)
    {
        $reviewCount = ReviewLink::select('review_links.*', 'review_sites.name')
            ->join('review_sites', 'review_links.site_id', '=', 'review_sites.id')
            ->whereYear('review_links.review_date', $year)
            ->whereMonth('review_links.review_date', $month)
            ->count();
        return $reviewCount;
    }

    public function getHighlightsFullList($dayInterval = 7)
    {
        $reviewLinks = DB::select('
            SELECT g.*, count(rl.id) AS recent_review_count
            FROM review_links rl
            JOIN games g ON rl.game_id = g.id
            JOIN review_sites rs ON rl.site_id = rs.id
            WHERE rl.review_date >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY rl.game_id
            ORDER BY g.eu_release_date DESC, g.id ASC
        ', [$dayInterval]);

        return $reviewLinks;
    }

    public function getHighlightsRecentlyRanked($dayInterval = 7, $limit = null)
    {
        $limitSql = '';
        if ($limit) {
            $limit = (int) $limit;
            if ($limit) {
                $limitSql = 'LIMIT '.$limit;
            }
        }
        $reviewLinks = DB::select('
            SELECT g.*, count(rl.id) AS recent_review_count
            FROM review_links rl
            JOIN games g ON rl.game_id = g.id
            JOIN review_sites rs ON rl.site_id = rs.id
            WHERE rl.review_date >= DATE_SUB(NOW(), INTERVAL ? DAY)
            AND g.review_count > 2
            AND g.format_digital != "De-listed"
            GROUP BY rl.game_id
            HAVING g.review_count - count(rl.id) < 3
            ORDER BY g.rating_avg DESC, g.eu_release_date DESC, g.id ASC
        '.$limitSql, [$dayInterval]);

        return $reviewLinks;
    }

    public function getHighlightsStillUnranked($dayInterval = 7)
    {
        $reviewLinks = DB::select('
            SELECT g.*, count(rl.id) AS recent_review_count
            FROM review_links rl
            JOIN games g ON rl.game_id = g.id
            JOIN review_sites rs ON rl.site_id = rs.id
            WHERE rl.review_date >= DATE_SUB(NOW(), INTERVAL ? DAY)
            AND g.review_count < 3
            GROUP BY rl.game_id
            ORDER BY g.rating_avg DESC, g.eu_release_date DESC, g.id ASC
        ', [$dayInterval]);

        return $reviewLinks;
    }

    public function getHighlightsAlreadyRanked($dayInterval = 7)
    {
        $reviewLinks = DB::select('
            SELECT g.*, count(rl.id) AS recent_review_count
            FROM review_links rl
            JOIN games g ON rl.game_id = g.id
            JOIN review_sites rs ON rl.site_id = rs.id
            WHERE rl.review_date >= DATE_SUB(NOW(), INTERVAL ? DAY)
            AND g.review_count > 2
            GROUP BY rl.game_id
            HAVING g.review_count - count(rl.id) > 2
            ORDER BY g.rating_avg DESC, g.eu_release_date DESC, g.id ASC
        ', [$dayInterval]);

        return $reviewLinks;
    }

    public function getLatestNaturalOrder($limit = 10)
    {
        return ReviewLink::orderBy('review_date', 'desc')->limit($limit)->get();
    }

    /**
     * @param $siteId
     * @param $limit
     * @return mixed
     * @deprecated
     */
    public function getLatestBySite($siteId, $limit = 20)
    {
        return ReviewLink::where('site_id', $siteId)
            ->orderBy('review_date', 'desc')
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getByGame($gameId)
    {
        $gameReviews = ReviewLink::select('review_links.*', 'review_sites.name')
            ->join('review_sites', 'review_links.site_id', '=', 'review_sites.id')
            ->where('game_id', $gameId)
            ->orderBy('review_links.rating_normalised', 'desc')
            ->orderBy('review_links.review_date', 'asc')
            ->orderBy('review_sites.last_review_date', 'desc')
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

    public function getSiteReviewStats($siteId)
    {
        $reviewAverage = DB::select("
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
        $reviewScores = DB::select("
            SELECT round(rating_normalised, 0) AS RatingValue, count(*) AS RatingCount
            FROM review_links
            WHERE site_id = ?
            GROUP BY round(rating_normalised, 0);
        ", [$siteId]);

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

    public function getFullScoreDistributionByYear($year)
    {
        $reviewScores = DB::select("
            SELECT round(rating_normalised, 0) AS RatingValue, count(*) AS RatingCount
            FROM review_links
            WHERE YEAR(review_date) = ?
            GROUP BY round(rating_normalised, 0);
        ", [$year]);

        if (!$reviewScores) return null;

        $scoresArray = [
            '0' => '0',
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

    public function getReviewCountStatsByYear($year)
    {
        $reviewCountStats = DB::select('
            SELECT review_count, count(*) AS count
            FROM games
            WHERE release_year = ?
            AND review_count != 0
            GROUP BY review_count
            ORDER BY review_count DESC
        ', [$year]);

        return $reviewCountStats;
    }

    public function getBySiteScore($siteId, $rating)
    {
        return ReviewLink::where('site_id', $siteId)
            ->where(DB::raw('round(rating_normalised, 0)'), $rating)
            ->get();
    }

    public function getMonthlyReviewsBySite($siteId)
    {
        return DB::select("
            SELECT DATE_FORMAT(review_date, '%Y-%m') AS review_month, count(*) AS count
            FROM review_links
            WHERE site_id = ?
            GROUP BY review_month, site_id
        ", [$siteId]);
    }
}