<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use OwenIt\Auditing\Contracts\Auditable;

class Game extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    const VIDEO_TYPE_NONE = 0;
    const VIDEO_TYPE_TRAILER = 1;
    const VIDEO_TYPE_GAMEPLAY = 2;

    /**
     * @var string
     */
    protected $table = 'games';

    /**
     * @var array
     */
    protected $fillable = [
        'title', 'link_title', 'price_eshop', 'players', 'rating_avg', 'review_count',
        'amazon_uk_link', 'game_rank', 'video_url',
        'boxart_square_url', 'eshop_europe_fs_id',
        'boxart_header_image', 'eshop_us_nsuid',
        'series_id', 'category_id',
        'image_square', 'image_header',
        'eu_released_on', 'eu_release_date', 'us_release_date', 'jp_release_date', 'eu_is_released', 'release_year',
        'format_digital', 'format_physical', 'format_dlc', 'format_demo',
        'eshop_europe_order', 'video_type'
    ];

    public function gameQualityScore()
    {
        return $this->hasOne('App\GameQualityScore', 'game_id', 'id');
    }

    public function gameRankYear()
    {
        return $this->hasOne('App\GameRankYear', 'game_id', 'id');
    }

    public function gameRankYearMonth()
    {
        return $this->hasOne('App\GameRankYearMonth', 'game_id', 'id');
    }

    public function category()
    {
        return $this->hasOne('App\Category', 'id', 'category_id');
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

    public function quickReviews()
    {
        return $this->hasMany('App\QuickReview', 'game_id', 'id');
    }

    public function gameTags()
    {
        return $this->hasMany('App\GameTag', 'game_id', 'id');
    }

    public function gameDevelopers()
    {
        return $this->hasMany('App\GameDeveloper', 'game_id', 'id');
    }

    public function gamePublishers()
    {
        return $this->hasMany('App\GamePublisher', 'game_id', 'id');
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

    public function dspNintendoCoUk()
    {
        return $this->hasMany('App\DataSourceParsed', 'game_id', 'id')
                    ->where('source_id', DataSource::DSID_NINTENDO_CO_UK);
    }

    public function dspWikipedia()
    {
        return $this->hasMany('App\DataSourceParsed', 'game_id', 'id')
            ->where('source_id', DataSource::DSID_WIKIPEDIA);
    }

    public function getVideoTypeDesc()
    {
        switch ($this->video_type) {
            case self::VIDEO_TYPE_TRAILER:
                $videoType = 'trailer';
                break;
            case self::VIDEO_TYPE_GAMEPLAY:
                $videoType = 'gameplay';
                break;
            default:
                $videoType = '';
        }

        return $videoType;
    }
}
