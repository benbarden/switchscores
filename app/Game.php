<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    /**
     * @var string
     */
    protected $table = 'games';

    /**
     * @var bool
     */
    public $timestamps = false;

    public function charts()
    {
        return $this->hasMany('App\ChartsRanking', 'game_id', 'id');
    }

    public function images()
    {
        return $this->hasMany('App\GameImage', 'game_id', 'id');
    }

    public function reviews()
    {
        return $this->hasMany('App\GameReview', 'game_id', 'id');
    }
}
