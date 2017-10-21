<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ChartsRankingGlobal extends Model
{
    const COUNTRY_EU = 'eu';
    const COUNTRY_US = 'us';

    /**
     * @var string
     */
    protected $table = 'charts_rankings_global';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var array
     */
    protected $fillable = [
        'chart_date', 'position', 'game_id', 'country_code'
    ];

    public function game()
    {
        return $this->hasOne('App\Game', 'id', 'game_id');
    }

    public function getMovement()
    {
        $chartDate = $this->chart_date;
        $gameId = $this->game_id;
        $countryCode = $this->country_code;

        $previousChartsDate = ChartsDate::where('chart_date', '<', $chartDate)->orderBy('chart_date', 'DESC')->limit(1)->get();

        if (count($previousChartsDate) == 0) {
            $dummyClass = new \stdClass();
            $dummyClass->position = 'N/A';
            return $dummyClass;
        }

        $previousDate = $previousChartsDate->first()->chart_date;

        $previousRank = ChartsRankingGlobal::where('chart_date', $previousDate)
            ->where('country_code', $countryCode)
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
