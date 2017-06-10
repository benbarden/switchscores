<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ChartsRankingUs extends Model
{
    /**
     * @var string
     */
    protected $table = 'charts_rankings_us';

    /**
     * @var bool
     */
    public $timestamps = false;

    public function game()
    {
        return $this->hasOne('App\Game', 'id', 'game_id');
    }

    public function getMovement()
    {
        $chartDate = $this->chart_date;
        $gameId = $this->game_id;

        $previousChartsDate = ChartsDate::
            where('chart_date', '<', $chartDate)
            ->where('chart_date', '>=', '2017-06-03')
            ->orderBy('chart_date', 'DESC')->limit(1)->get();

        if (count($previousChartsDate) == 0) {
            $dummyClass = new \stdClass();
            $dummyClass->position = 'N/A';
            return $dummyClass;
        }

        $previousDate = $previousChartsDate->first()->chart_date;

        $previousRank = ChartsRankingUs::where('chart_date', $previousDate)
            ->where('game_id', $gameId)
            ->first();

        if (!$previousRank) {
            $dummyClass = new \stdClass();
            $dummyClass->position = '0';
            return $dummyClass;
        } else {
            return $previousRank;
        }

    }
}
