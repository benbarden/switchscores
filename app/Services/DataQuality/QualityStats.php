<?php

namespace App\Services\DataQuality;

use App\Game;
use Illuminate\Support\Facades\DB;

class QualityStats
{
    // *** Categories - all months *** //
    public function getCategoryStats()
    {
        return DB::select('
            SELECT
            DATE_FORMAT(eu_release_date, \'%Y-%m\') AS yearmonth,
            DATE_FORMAT(eu_release_date, \'%Y\') AS release_year,
            DATE_FORMAT(eu_release_date, \'%m\') AS release_month,
            count(category_id) AS has_category, 
            count(*) - count(category_id) AS no_category, 
            count(*) AS total_count,
            round((count(category_id) / count(*)) * 100, 2) AS pc_done
            FROM games
            GROUP BY yearmonth
            ORDER BY yearmonth DESC
        ');
    }

    public function getGamesWithCategory($year, $month)
    {
        return Game::whereNotNull('category_id')
            ->whereYear('eu_release_date', $year)
            ->whereMonth('eu_release_date', $month)
            ->orderBy('eu_release_date', 'asc')
            ->get();
    }

    public function getGamesWithoutCategory($year, $month)
    {
        return Game::whereNull('category_id')
            ->whereYear('eu_release_date', $year)
            ->whereMonth('eu_release_date', $month)
            ->orderBy('eu_release_date', 'asc')
            ->get();
    }

    // Categories
    // All-time stats
    public function countGamesWithCategory()
    {
        return Game::whereNotNull('category_id')->count();
    }

    public function countGamesWithoutCategory()
    {
        return Game::whereNull('category_id')->count();
    }

    // Series
    // All-time stats
    public function countGamesWithSeries()
    {
        return Game::whereNotNull('series_id')->count();
    }

    public function countGamesWithoutSeries()
    {
        return Game::whereNull('series_id')->count();
    }

}