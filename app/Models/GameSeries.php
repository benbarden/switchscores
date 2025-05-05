<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameSeries extends Model
{
    /**
     * @var string
     */
    protected $table = 'game_series';

    /**
     * @var array
     */
    protected $fillable = [
        'series', 'link_title', 'landing_image'
    ];

    public function games()
    {
        return $this->hasMany('App\Models\Game', 'series_id', 'id');
    }

    public function gamesSwitch1()
    {
        return $this->hasMany('App\Models\Game', 'series_id', 'id')
            ->where('console_id', Console::ID_SWITCH_1);
    }

    public function gamesSwitch2()
    {
        return $this->hasMany('App\Models\Game', 'series_id', 'id')
            ->where('console_id', Console::ID_SWITCH_2);
    }
}
