<?php

namespace App\Services\DataQuality;

use App\Game;
use Illuminate\Support\Facades\DB;

class QualityStats
{
    // *** Primary types - all months *** //
    public function getPrimaryTypeStats()
    {
        return DB::select('
            SELECT
            DATE_FORMAT(eu_release_date, \'%Y-%m\') AS yearmonth,
            count(primary_type_id) AS has_primary_type, 
            count(*) - count(primary_type_id) AS no_primary_type, 
            count(*) AS total_count,
            round((count(primary_type_id) / count(*)) * 100, 2) AS pc_done
            FROM games
            GROUP BY yearmonth
            ORDER BY yearmonth DESC
        ');
    }

    // Primary types
    // All-time stats
    public function countGamesWithPrimaryType()
    {
        return Game::whereNotNull('primary_type_id')->count();
    }

    public function countGamesWithoutPrimaryType()
    {
        return Game::whereNull('primary_type_id')->count();
    }

    // Monthly stats
    public function countGamesWithPrimaryTypeByYearMonth($year, $month)
    {
        return Game::whereNotNull('primary_type_id')->whereYear('eu_release_date', $year)->whereMonth('eu_release_date', $month)->count();
    }

    public function countGamesWithoutPrimaryTypeByYearMonth($year, $month)
    {
        return Game::whereNull('primary_type_id')->whereYear('eu_release_date', $year)->whereMonth('eu_release_date', $month)->count();
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