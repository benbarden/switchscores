<?php


namespace App\Services;

use App\ChartsRankingGlobal;


class ChartsRankingGlobalService
{
    public function getByCountryAndDate($countryCode, $date)
    {
        $gamesList = ChartsRankingGlobal::
            where('country_code', $countryCode)
            ->where('chart_date', $date)
            ->orderBy('position', 'asc')
            ->get();
        return $gamesList;
    }

    public function getByCountryAndGame($countryCode, $gameId)
    {
        $gamesList = ChartsRankingGlobal::
            where('country_code', $countryCode)
            ->where('game_id', $gameId)
            ->orderBy('chart_date', 'desc')
            ->get();
        return $gamesList;
    }

    public function getByGameEu($gameId)
    {
        $countryCode = ChartsRankingGlobal::COUNTRY_EU;
        return $this->getByCountryAndGame($countryCode, $gameId);
    }

    public function getByGameUs($gameId)
    {
        $countryCode = ChartsRankingGlobal::COUNTRY_US;
        return $this->getByCountryAndGame($countryCode, $gameId);
    }
}