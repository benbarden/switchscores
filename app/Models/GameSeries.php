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
}
