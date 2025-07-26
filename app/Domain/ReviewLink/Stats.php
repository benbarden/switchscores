<?php

namespace App\Domain\ReviewLink;

use App\Domain\Repository\AbstractRepository;
use App\Enums\CacheDuration;
use App\Models\Game;
use App\Models\QuickReview;
use App\Models\ReviewLink;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Stats extends AbstractRepository
{
    protected function getCachePrefix(): string
    {
        return 'reviewlinkstats';
    }

    public function totalOverall()
    {
        return ReviewLink::count();
    }

    public function totalBySite($siteId)
    {
        return ReviewLink::where('site_id', $siteId)->count();
    }

    public function totalByUser($userId)
    {
        return ReviewLink::where('user_id', $userId)->count();
    }

    public function totalActiveByConsoleYearMonth($consoleId, $year, $month)
    {
        $cacheKey = $this->buildCacheKey("c$consoleId-$year-$month-total");
        return $this->rememberCache($cacheKey, CacheDuration::ONE_DAY, function() use ($consoleId, $year, $month) {
            $result = DB::select("
                SELECT count(*) AS count
                FROM review_links rl
                JOIN games g on rl.game_id = g.id
                WHERE g.console_id = ?
                AND YEAR(rl.review_date) = ?
                AND MONTH(rl.review_date) = ?
        ", [$consoleId, $year, $month]);
            if ($result) {
                return $result[0]->count;
            } else {
                return 0;
            }
        });
    }

    /**
     * @deprecated
     * @param $year
     * @param $month
     * @return mixed
     */
    public function totalActiveByYearMonth($year, $month)
    {
        return ReviewLink::whereYear('review_links.review_date', $year)
            ->whereMonth('review_links.review_date', $month)
            ->count();
    }

    public function reviewCountStatsByConsoleAndYear($consoleId, $year)
    {
        return DB::select('
            SELECT review_count, count(*) AS count
            FROM games
            WHERE console_id = ?
            AND release_year = ?
            AND review_count != 0
            GROUP BY review_count
            ORDER BY review_count DESC
        ', [$consoleId, $year]);
    }

    /**
     * @deprecated
     * @param $year
     * @return array
     */
    public function reviewCountStats($year)
    {
        return DB::select('
            SELECT review_count, count(*) AS count
            FROM games
            WHERE release_year = ?
            AND review_count != 0
            GROUP BY review_count
            ORDER BY review_count DESC
        ', [$year]);
    }

    public function monthlyCountBySite($siteId)
    {
        return DB::select("
            SELECT DATE_FORMAT(review_date, '%Y-%m') AS review_month, count(*) AS count
            FROM review_links
            WHERE site_id = ?
            GROUP BY review_month, site_id
        ", [$siteId]);
    }

    public function scoreDistributionBySite($siteId)
    {
        $reviewScores = DB::select("
            SELECT round(rating_normalised, 0) AS RatingValue, count(*) AS RatingCount
            FROM review_links
            WHERE site_id = ?
            GROUP BY round(rating_normalised, 0);
        ", [$siteId]);

        return $this->scoreDistribution($reviewScores);
    }

    public function scoreDistributionByConsoleAndYear($consoleId, $year)
    {
        $reviewScores = DB::select("
            SELECT round(rating_normalised, 0) AS RatingValue, count(*) AS RatingCount
            FROM review_links rl
            JOIN games g ON rl.game_id = g.id
            WHERE g.console_id = ?
            AND YEAR(review_date) = ?
            GROUP BY round(rating_normalised, 0);
        ", [$consoleId, $year]);

        return $this->scoreDistribution($reviewScores);
    }

    /**
     * @deprecated
     * @param $year
     * @return string[]|null
     */
    public function scoreDistributionByYear($year)
    {
        $reviewScores = DB::select("
            SELECT round(rating_normalised, 0) AS RatingValue, count(*) AS RatingCount
            FROM review_links
            WHERE YEAR(review_date) = ?
            GROUP BY round(rating_normalised, 0);
        ", [$year]);

        return $this->scoreDistribution($reviewScores);
    }

    public function scoreDistribution($reviewScores)
    {
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

    public function calculateReviewCount(Collection $reviewLinks, Collection $quickReviews = null)
    {
        $reviewLinkCount = 0;
        $quickReviewCount = 0;
        if ($reviewLinks) {
            $reviewLinkCount = $reviewLinks->count();
        }
        if ($quickReviews) {
            $quickReviewCount = $quickReviews->count();
        }
        $totalReviewCount = $reviewLinkCount + $quickReviewCount;
        return $totalReviewCount;
    }

    public function calculateReviewAverage(Collection $reviewLinks, Collection $quickReviews = null)
    {
        $reviewLinkCount = 0;
        $quickReviewCount = 0;
        if ($reviewLinks) {
            $reviewLinkCount = $reviewLinks->count();
        }
        if ($quickReviews) {
            $quickReviewCount = $quickReviews->count();
        }

        $reviewCount = $reviewLinkCount + $quickReviewCount;

        if ($reviewCount == 0) return null;

        $sumTotal = 0;

        if ($reviewLinks) {
            foreach ($reviewLinks as $review) {
                $reviewScore = $review->rating_normalised;
                $sumTotal += $reviewScore;
            }
        }

        if ($quickReviews) {
            foreach ($quickReviews as $review) {
                $reviewScore = $review->review_score;
                $sumTotal += $reviewScore;
            }
        }

        $avgScore = round(($sumTotal / $reviewCount), 2);
        $avgScore = number_format($avgScore, 2);

        return $avgScore;
    }

    public function calculateStats($reviewLinks, $quickReviews)
    {
        $reviewCount = $this->calculateReviewCount($reviewLinks, $quickReviews);
        $reviewAverage = $this->calculateReviewAverage($reviewLinks, $quickReviews);

        return [$reviewCount, $reviewAverage];
    }

    public function updateStats(Game $game)
    {
        $gameReviewLinks = $game->reviews;
        $gameQuickReviews = $game->quickReviews->where('item_status', QuickReview::STATUS_ACTIVE);

        $gameReviewStats = $this->calculateStats($gameReviewLinks, $gameQuickReviews);

        $game->review_count = $gameReviewStats[0];
        $game->rating_avg = $gameReviewStats[1];
        $game->save();
    }
}