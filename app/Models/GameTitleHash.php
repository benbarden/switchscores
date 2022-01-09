<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameTitleHash extends Model
{
    /**
     * @var string
     */
    protected $table = 'game_title_hashes';

    /**
     * @var array
     */
    protected $fillable = [
        'title', 'title_hash', 'game_id',
    ];

    public function game()
    {
        return $this->hasOne('App\Models\Game', 'id', 'game_id');
    }
}
