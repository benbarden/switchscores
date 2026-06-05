<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameFlag extends Model
{
    protected $table = 'game_flags';

    protected $fillable = ['game_id', 'flag', 'notes'];

    public function game()
    {
        return $this->hasOne('App\Models\Game', 'id', 'game_id');
    }
}
