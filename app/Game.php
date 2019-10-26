<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    /**
     * @var string
     */
    protected $table = 'games';

    /**
     * @var bool
     */
    public $timestamps = true;

    /**
     * @var array
     */
    protected $fillable = [
        'title', 'link_title', 'price_eshop', 'players',
        'rating_avg', 'review_count', 'overview', 'image_count', 'developer', 'publisher',
        'media_folder', 'amazon_uk_link', 'game_rank', 'video_url',
        'boxart_square_url', 'eshop_europe_fs_id',
        'boxart_header_image', 'eshop_us_nsuid', 'video_header_text',
        'primary_type_id', 'series_id',
    ];

    public function gameRankYear()
    {
        return $this->hasOne('App\GameRankYear', 'game_id', 'id');
    }

    public function gameRankYearMonth()
    {
        return $this->hasOne('App\GameRankYearMonth', 'game_id', 'id');
    }

    public function primaryType()
    {
        return $this->hasOne('App\GamePrimaryType', 'id', 'primary_type_id');
    }

    public function series()
    {
        return $this->hasOne('App\GameSeries', 'id', 'series_id');
    }

    public function charts()
    {
        return $this->hasMany('App\ChartsRanking', 'game_id', 'id');
    }

    public function images()
    {
        return $this->hasMany('App\GameImage', 'game_id', 'id');
    }

    public function titleHashes()
    {
        return $this->hasMany('App\GameTitleHash', 'game_id', 'id');
    }

    public function reviews()
    {
        return $this->hasMany('App\ReviewLink', 'game_id', 'id');
    }

    public function gameTags()
    {
        return $this->hasMany('App\GameTag', 'game_id', 'id');
    }

    public function gameGenres()
    {
        return $this->hasMany('App\GameGenre', 'game_id', 'id');
    }

    public function releaseDates()
    {
        return $this->hasMany('App\GameReleaseDate', 'game_id', 'id');
    }

    public function gameDevelopers()
    {
        return $this->hasMany('App\GameDeveloper', 'game_id', 'id');
    }

    public function gamePublishers()
    {
        return $this->hasMany('App\GamePublisher', 'game_id', 'id');
    }

    // GameReleaseDate helper functions
    public function regionReleaseDate($region)
    {
        return $this->releaseDates()->where('region', '=', $region)->first();
    }

    public function eshopEuropeGame()
    {
        return $this->hasOne('App\EshopEuropeGame', 'fs_id', 'eshop_europe_fs_id');
    }

    public function eshopUSGame()
    {
        return $this->hasOne('App\EshopUSGame', 'nsuid', 'eshop_europe_nsuid');
    }
}
