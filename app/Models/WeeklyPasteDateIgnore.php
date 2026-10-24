<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeeklyPasteDateIgnore extends Model
{
    protected $table = 'weekly_paste_date_ignores';

    protected $fillable = ['game_id', 'release_date'];

    public function game()
    {
        return $this->belongsTo(Game::class, 'game_id');
    }
}
