<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GameGenre extends Model
{
    /**
     * @var string
     */
    protected $table = 'game_genres';

    /**
     * @var bool
     */
    public $timestamps = false;

    public function game()
    {
        return $this->hasOne('App\Game', 'id', 'game_id');
    }

    public function genre()
    {
        return $this->hasOne('App\Genre', 'id', 'genre_id');
    }

}
