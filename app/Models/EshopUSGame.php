<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EshopUSGame extends Model
{
    /**
     * @var string
     */
    protected $table = 'eshop_us_games';

    /**
     * @var array
     */
    protected $fillable = [
        'title',
        'categories',
        'slug',
        'buyitnow',
        'release_date',
        'digitaldownload',
        'nso',
        'free_to_start',
        'system',
        'ncom_id', // "id" in JSON source data
        'ca_price',
        'number_of_players',
        'nsuid',
        'video_link',
        'eshop_price',
        'front_box_art',
        'game_code',
        'buyonline',
        'sale_price',
        'release_date_display',
    ];

    public function game()
    {
        return $this->hasOne('App\Models\Game', 'eshop_us_fs_id', 'fs_id');
    }

}
