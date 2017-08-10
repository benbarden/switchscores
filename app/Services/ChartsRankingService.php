<?php


namespace App\Services;

use App\ChartsRanking;


class ChartsRankingService
{
    public function getByDate($date)
    {
        $gamesList = ChartsRanking::where('chart_date', $date)
            ->orderBy('position', 'asc')
            ->get();
        return $gamesList;
    }
}