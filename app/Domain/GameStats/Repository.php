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
    public function getFormatDigital()
    {
        return DB::select("
            SELECT format_digital AS format_desc, count(*) AS count
            FROM games
            GROUP BY format_digital
            ORDER BY format_digital
        ");
    }

    /**
     * @deprecated
     * @return integer
     */
    public function getFormatPhysical()
    {
        return DB::select("
            SELECT format_physical AS format_desc, count(*) AS count
            FROM games
            GROUP BY format_physical
            ORDER BY format_physical
        ");
    }

    /**
     * @deprecated
     * @return integer
     */
    public function getFormatDLC()
    {
        return DB::select("
            SELECT format_dlc AS format_desc, count(*) AS count
            FROM games
            GROUP BY format_dlc
            ORDER BY format_dlc
        ");
    }

    /**
     * @deprecated
     * @return integer
     */
    public function getFormatDemo()
    {
        return DB::select("
            SELECT format_demo AS format_desc, count(*) AS count
            FROM games
            GROUP BY format_demo
            ORDER BY format_demo
        ");
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
    public function totalNoAmazonUkLink()
    {
        return Game::where('format_physical', Game::FORMAT_AVAILABLE)->whereNull('amazon_uk_link')->count();
    }

    /**
     * @deprecated
     * @return integer
     */
    public function totalNoAmazonUsLink()
    {
        return Game::where('format_physical', Game::FORMAT_AVAILABLE)->whereNull('amazon_us_link')->count();
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
}