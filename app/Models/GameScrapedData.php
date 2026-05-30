<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameScrapedData extends Model
{
    /**
     * @var string
     */
    protected $table = 'game_scraped_data';

    /**
     * @var array
     */
    protected $fillable = [
        'game_id',
        'players_local',
        'players_wireless',
        'players_online',
        'multiplayer_mode',
        'features_json',
        'header_image_url',
        'header_image_size',
        'scraped_at',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'features_json' => 'array',
        'scraped_at' => 'datetime',
    ];

    public function game()
    {
        return $this->belongsTo('App\Models\Game', 'game_id', 'id');
    }
}
