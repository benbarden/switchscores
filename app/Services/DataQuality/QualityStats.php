<?php

namespace App\Services\DataQuality;

use App\Game;

class QualityStats
{
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