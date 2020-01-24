<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use OwenIt\Auditing\Contracts\Auditable;

class Game extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

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
        'rating_avg', 'review_count', 'developer', 'publisher',
        'amazon_uk_link', 'game_rank', 'video_url',
        'boxart_square_url', 'eshop_europe_fs_id',
        'boxart_header_image', 'eshop_us_nsuid', 'video_header_text',
        'primary_type_id', 'series_id', 'eu_released_on',
        'eu_release_date', 'us_release_date', 'jp_release_date', 'eu_is_released', 'release_year'
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

    public function importRuleEshop()
    {
        return $this->hasOne('App\GameImportRuleEshop', 'game_id', 'id');
    }

    public function importRuleWikipedia()
    {
        return $this->hasOne('App\GameImportRuleWikipedia', 'game_id', 'id');
    }
}
