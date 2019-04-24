<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GameRankAllTime extends Model
{
    /**
     * @var string
     */
    protected $table = 'game_rank_alltime';

    /**
     * @var bool
     */
    public $timestamps = true;

    /**
     * @var array
     */
    protected $fillable = [
        'game_rank', 'game_id',
    ];

    public function game()
    {
        return $this->hasOne('App\Game', 'id', 'game_id');
    }
}
