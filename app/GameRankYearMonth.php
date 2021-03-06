<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GameRankYearMonth extends Model
{
    /**
     * @var string
     */
    protected $table = 'game_rank_yearmonth';

    /**
     * @var array
     */
    protected $fillable = [
        'release_yearmonth', 'game_rank', 'game_id',
    ];

    public function game()
    {
        return $this->hasOne('App\Game', 'id', 'game_id');
    }
}
