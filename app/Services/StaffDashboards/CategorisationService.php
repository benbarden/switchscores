<?php

namespace App\Services\StaffDashboards;

use App\Game;

class CategorisationService
{
    public function countGamesWithPrimaryType()
    {
        return Game::whereNotNull('primary_type_id')->count();
    }

    public function countGamesWithoutPrimaryType()
    {
        return Game::whereNull('primary_type_id')->count();
    }

    public function countGamesWithSeries()
    {
        return Game::whereNotNull('series_id')->count();
    }

    public function countGamesWithoutSeries()
    {
        return Game::whereNull('series_id')->count();
    }
}