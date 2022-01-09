<?php

namespace App\Models;

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
        return $this->hasOne('App\Models\Game', 'id', 'game_id');
    }
}
