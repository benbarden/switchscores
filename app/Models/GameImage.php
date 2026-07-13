<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameImage extends Model
{
    const LOCATION_LEGACY = 'legacy';
    const LOCATION_SPACES = 'spaces';

    protected $table = 'game_images';

    protected $fillable = [
        'game_id', 'square_filename', 'header_filename', 'location',
        'square_updated_at', 'header_updated_at',
    ];

    protected $casts = [
        'square_updated_at' => 'datetime',
        'header_updated_at' => 'datetime',
    ];

    public function game()
    {
        return $this->hasOne('App\Models\Game', 'id', 'game_id');
    }
}
