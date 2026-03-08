<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserIgnoredGame extends Model
{
    protected $table = 'user_ignored_games';

    protected $fillable = [
        'user_id',
        'game_id',
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }

    public function game()
    {
        return $this->belongsTo('App\Models\Game', 'game_id', 'id');
    }
}
