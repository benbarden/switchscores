<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GameRankUpdate extends Model
{
    /**
     * @var string
     */
    protected $table = 'game_rank_updates';

    /**
     * @var bool
     */
    public $timestamps = true;

    /**
     * @var array
     */
    protected $fillable = [
        'game_id', 'rank_old', 'rank_new', 'movement', 'rating_avg',
    ];

    public function game()
    {
        return $this->hasOne('App\Game', 'id', 'game_id');
    }
}
