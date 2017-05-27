<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ChartsRanking extends Model
{
    /**
     * @var string
     */
    protected $table = 'charts_rankings';

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

        $previousChartsDate = ChartsDate::where('chart_date', '<', $chartDate)->orderBy('chart_date', 'DESC')->limit(1)->get();

        if (count($previousChartsDate) == 0) {
            $dummyClass = new \stdClass();
            $dummyClass->position = 'N/A';
            return $dummyClass;
        }

        $previousDate = $previousChartsDate->first()->chart_date;

        $previousRank = ChartsRanking::where('chart_date', $previousDate)
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
