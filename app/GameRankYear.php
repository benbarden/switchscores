<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GameRankYear extends Model
{
    /**
     * @var string
     */
    protected $table = 'game_rank_year';

    /**
     * @var array
     */
    protected $fillable = [
        'release_year', 'game_rank', 'game_id',
    ];

    public function game()
    {
        return $this->hasOne('App\Game', 'id', 'game_id');
    }
}
