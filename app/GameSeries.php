<?php

namespace App;

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
        return $this->hasMany('App\Game', 'series_id', 'id');
    }
}
