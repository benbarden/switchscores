<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SteamGemExclusion extends Model
{
    protected $table = 'steam_gem_exclusions';

    protected $fillable = ['game_id', 'reason'];

    public static array $reasons = [
        'too-well-known'   => 'Too well known',
        'low-quality'      => 'Low quality content',
        'not-a-good-fit'   => 'Not a good fit',
    ];

    public function game()
    {
        return $this->belongsTo(Game::class);
    }
}
