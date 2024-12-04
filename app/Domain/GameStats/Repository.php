<?php


namespace App\Domain\GameStats;


use App\Models\Game;
use Illuminate\Support\Facades\DB;

class Repository
{
    /**
     * @return integer
     */
    public function grandTotal()
    {
        return Game::orderBy('title', 'asc')->count();
    }

    /**
     * @return integer
     */
    public function totalReleased()
    {
        return Game::where('eu_is_released', 1)->where('format_digital', '<>', Game::FORMAT_DELISTED)->count();
    }

    /**
     * @return integer
     */
    public function totalLowQuality()
    {
        return Game::where('eu_is_released', 1)->where('is_low_quality', '1')->count();
    }

    /**
     * @return integer
     */
    public function totalRanked()
    {
        return Game::whereNotNull('game_rank')->count();
    }

    /**
     * @return integer
     */
    public function totalNoCategoryExcludingLowQuality()
    {
        return Game::whereNull('category_id')->where('is_low_quality', 0)->count();
    }

    public function totalNoCategoryAll()
    {
        return Game::whereNull('category_id')->count();
    }

    /**
     * @return integer
     */
    public function totalUntagged()
    {
        return Game::whereDoesntHave('gameTags')->count();
    }

    /**
     * @return integer
     */
    public function totalUpcoming()
    {
        return Game::where('eu_is_released', 0)->count();
    }

    /**
     * @return integer
     */
    public function totalToBeReleased()
    {
        $games = Game::where('eu_is_released', 0)
            ->whereRaw('DATE(games.eu_release_date) <= CURDATE()')
            ->count();

        return $games;
    }

    public function getFormatDigital()
    {
        return DB::select("
            SELECT format_digital AS format_desc, count(*) AS count
            FROM games
            GROUP BY format_digital
            ORDER BY format_digital
        ");
    }

    public function getFormatPhysical()
    {
        return DB::select("
            SELECT format_physical AS format_desc, count(*) AS count
            FROM games
            GROUP BY format_physical
            ORDER BY format_physical
        ");
    }

    public function getFormatDLC()
    {
        return DB::select("
            SELECT format_dlc AS format_desc, count(*) AS count
            FROM games
            GROUP BY format_dlc
            ORDER BY format_dlc
        ");
    }

    public function getFormatDemo()
    {
        return DB::select("
            SELECT format_demo AS format_desc, count(*) AS count
            FROM games
            GROUP BY format_demo
            ORDER BY format_demo
        ");
    }

    public function totalNoVideoType()
    {
        return Game::whereNull('video_type')->count();
    }

    public function totalNoAmazonUkLink()
    {
        return Game::where('format_physical', Game::FORMAT_AVAILABLE)->whereNull('amazon_uk_link')->count();
    }

    public function totalNoAmazonUsLink()
    {
        return Game::where('format_physical', Game::FORMAT_AVAILABLE)->whereNull('amazon_us_link')->count();
    }

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