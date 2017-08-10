<?php


namespace App\Services;

use App\ChartsRankingUs;


class ChartsRankingUsService
{
    public function getByDate($date)
    {
        $gamesList = ChartsRankingUs::where('chart_date', $date)
            ->orderBy('position', 'asc')
            ->get();
        return $gamesList;
    }
}