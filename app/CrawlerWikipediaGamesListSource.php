<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CrawlerWikipediaGamesListSource extends Model
{
    /**
     * @var string
     */
    protected $table = 'crawler_wikipedia_games_list_source';

    /**
     * @var bool
     */
    public $timestamps = true;

    /**
     * @var array
     */
    protected $fillable = [
        'title', 'genres', 'developers', 'publishers',
        'release_date_eu', 'upcoming_date_eu', 'is_released_eu',
        'release_date_us', 'upcoming_date_us', 'is_released_us',
        'release_date_jp', 'upcoming_date_jp', 'is_released_jp',
    ];

}
