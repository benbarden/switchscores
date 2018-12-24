<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserGamesCollection extends Model
{
    /**
     * @var string
     */
    protected $table = 'user_games_collection';

    /**
     * @var bool
     */
    public $timestamps = true;

    /**
     * @var array
     */
    protected $fillable = [
        'user_id', 'game_id', 'owned_from', 'owned_type',
        'is_started', 'is_ongoing', 'is_complete', 'hours_played'
    ];

    public function game()
    {
        return $this->hasOne('App\Game', 'id', 'game_id');
    }
}
