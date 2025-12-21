<?php


namespace App\Domain\GameStats;


use App\Domain\Cache\CacheManager;
use App\Domain\Repository\AbstractRepository;
use App\Enums\CacheDuration;
use App\Models\Game;

use Illuminate\Support\Facades\DB;

class Repository extends AbstractRepository
{
    protected function getCachePrefix(): string
    {
        return "gamestats";
    }

    /**
     * @deprecated
     * @return integer
     */
    public function grandTotal()
    {
        return Game::orderBy('title', 'asc')->count();
    }

    /**
     * @deprecated
     * @return integer
     */
    public function totalReleased()
    {
        return Game::where('eu_is_released', 1)->where('format_digital', '<>', Game::FORMAT_DELISTED)->count();
    }

    /**
     * @deprecated
     * @return integer
     */
    public function totalRanked()
    {
        return Game::whereNotNull('game_rank')->where('format_digital', '<>', Game::FORMAT_DELISTED)->count();
    }

    public function clearCacheTotalRanked($consoleId)
    {
        $cacheKey = $this->buildCacheKey("$consoleId-total-ranked");
        $this->clearCache($cacheKey);
    }

    public function totalRankedByConsole($consoleId)
    {
        $cacheKey = $this->buildCacheKey("$consoleId-total-ranked");
        return $this->rememberCache($cacheKey, CacheDuration::ONE_DAY, function() use ($consoleId) {
            return Game::where('console_id', $consoleId)->whereNotNull('game_rank')->where('format_digital', '<>', Game::FORMAT_DELISTED)->count();
        });
    }

    /**
     * @deprecated
     * @return integer
     */
    public function totalNoCategoryExcludingLowQuality()
    {
        return Game::whereNull('category_id')->where('is_low_quality', 0)->count();
    }

    /**
     * @deprecated
     * @return integer
     */
    public function totalNoCategoryAll()
    {
        return Game::whereNull('category_id')->count();
    }

    /**
     * @deprecated
     * @return integer
     */
    public function totalNoCategoryWithCollectionId()
    {
        return Game::whereNotNull('collection_id')->whereNull('category_id')->count();
    }

    /**
     * @deprecated
     * @return integer
     */
    public function totalNoCategoryWithReviews()
    {
        return Game::where('review_count', '>', 0)->whereNull('category_id')->count();
    }

    /**
     * @deprecated
     * @return integer
     */
    public function totalUntagged()
    {
        return Game::whereDoesntHave('gameTags')->count();
    }

    /**
     * @deprecated
     * @return integer
     */
    public function totalUpcoming()
    {
        return Game::where('eu_is_released', 0)->count();
    }

    /**
     * @deprecated
     * @return integer
     */
    public function totalToBeReleased()
    {
        $games = Game::where('eu_is_released', 0)
            ->whereRaw('DATE(games.eu_release_date) <= CURDATE()')
            ->count();

        return $games;
    }

    /**
     * @deprecated
     * @return integer
     */
    public function totalNoVideoType()
    {
        return Game::whereNull('video_type')->count();
    }

    /**
     * @deprecated
     * @return integer
     */
    public function totalNoPrice()
    {
        return Game::whereNull('price_eshop')->count();
    }

    /**
     * @deprecated
     * @return integer
     */
    public function totalYearWeekStandardQuality($year, $week)
    {
        // fix: week needs to be -1 as MySQL is zero-indexed?
        $week--;

        if ($week < 10) {
            $week = str_pad($week, 2, '0', STR_PAD_LEFT);
        }
        return Game::where('eu_is_released', 1)
            ->where(DB::raw('YEARWEEK(eu_release_date)'), $year.$week)
            ->where('is_low_quality', 0)
            ->orderBy('games.eu_release_date')
            ->orderBy('games.title')
            ->count();
    }

    /**
     * @deprecated
     * @return integer
     */
    public function totalYearWeekLowQuality($year, $week)
    {
        // fix: week needs to be -1 as MySQL is zero-indexed?
        $week--;

        if ($week < 10) {
            $week = str_pad($week, 2, '0', STR_PAD_LEFT);
        }
        return Game::where('eu_is_released', 1)
            ->where(DB::raw('YEARWEEK(eu_release_date)'), $year.$week)
            ->where('is_low_quality', 1)
            ->orderBy('games.eu_release_date')
            ->orderBy('games.title')
            ->count();
    }

    public function totalCategoryUnverified()
    {
        return Game::where('category_verification', Game::VERIF_UNVERIFIED)->count();
    }

    public function totalCategoryVerified()
    {
        return Game::where('category_verification', Game::VERIF_VERIFIED)->count();
    }

    public function totalCategoryNeedsReview()
    {
        return Game::where('category_verification', Game::VERIF_NEEDS_REVIEW)->count();
    }

    public function totalTagsUnverified()
    {
        return Game::where('tags_verification', Game::VERIF_UNVERIFIED)->count();
    }

    public function totalTagsVerified()
    {
        return Game::where('tags_verification', Game::VERIF_VERIFIED)->count();
    }

    public function totalTagsNeedsReview()
    {
        return Game::where('tags_verification', Game::VERIF_NEEDS_REVIEW)->count();
    }
}