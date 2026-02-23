<?php

namespace App\Models;

use App\Enums\GameStatus;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Game extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    const FORMAT_AVAILABLE = 'Available';
    const FORMAT_INCLUDED_IN_BUNDLE = 'Included in bundle';
    const FORMAT_LIMITED_EDITION = 'Limited edition';
    const FORMAT_NOT_AVAILABLE = 'Not available';
    const FORMAT_DELISTED = 'De-listed';

    const VIDEO_TYPE_NONE = 0;
    const VIDEO_TYPE_TRAILER = 1;
    const VIDEO_TYPE_GAMEPLAY = 2;

    const VERIF_UNVERIFIED = 0;
    const VERIF_VERIFIED = 1;
    const VERIF_NEEDS_REVIEW = 2;

    /**
     * @var string
     */
    protected $table = 'games';

    /**
     * @var array
     */
    protected $fillable = [
        'title', 'link_title', 'console_id', 'price_eshop', 'players', 'rating_avg', 'review_count',
        'amazon_uk_link', 'amazon_us_link', 'game_rank', 'video_url',
        'boxart_square_url', 'eshop_europe_fs_id',
        'boxart_header_image', 'eshop_us_nsuid',
        'series_id', 'category_id', 'collection_id',
        'category_verification', 'tags_verification',
        'image_square', 'image_header',
        'eu_released_on', 'eu_release_date', 'us_release_date', 'jp_release_date', 'eu_is_released', 'release_year',
        'format_digital', 'format_physical', 'format_dlc', 'format_demo', 'game_status',
        'eshop_europe_order', 'video_type', 'price_eshop_discounted', 'price_eshop_discount_pc',
        'is_low_quality', 'taxonomy_needs_review', 'packshot_square_url_override', 'game_description', 'one_to_watch',
        'added_batch_date', 'amazon_uk_status', 'amazon_us_status', 'amazon_uk_asin', 'amazon_us_asin',
        'last_crawled_at', 'last_crawl_status'
    ];

    protected $casts = [
        'category_verification' => 'integer',
        'tags_verification' => 'integer',
        'game_status' => GameStatus::class,
        'last_crawled_at' => 'datetime',
    ];

    public function console()
    {
        return $this->hasOne('App\Models\Console', 'id', 'console_id');
    }

    public function category()
    {
        return $this->hasOne('App\Models\Category', 'id', 'category_id');
    }

    public function series()
    {
        return $this->hasOne('App\Models\GameSeries', 'id', 'series_id');
    }

    public function gameCollection()
    {
        return $this->hasOne('App\Models\GameCollection', 'id', 'collection_id');
    }

    public function titleHashes()
    {
        return $this->hasMany('App\Models\GameTitleHash', 'game_id', 'id');
    }

    public function reviews()
    {
        return $this->hasMany('App\Models\ReviewLink', 'game_id', 'id');
    }

    public function quickReviews()
    {
        return $this->hasMany('App\Models\QuickReview', 'game_id', 'id');
    }

    public function gameTags()
    {
        return $this->hasMany('App\Models\GameTag', 'game_id', 'id');
    }

    public function gameDevelopers()
    {
        return $this->hasMany('App\Models\GameDeveloper', 'game_id', 'id');
    }

    public function gamePublishers()
    {
        return $this->hasMany('App\Models\GamePublisher', 'game_id', 'id');
    }

    public function crawlLifecycle()
    {
        return $this->hasMany('App\Models\GameCrawlLifecycle', 'game_id', 'id');
    }

    public function gameQualityScore()
    {
        return $this->hasOne('App\Models\GameQualityScore', 'game_id', 'id');
    }

    public function gameRankYear()
    {
        return $this->hasOne('App\Models\GameRankYear', 'game_id', 'id');
    }

    public function gameRankYearMonth()
    {
        return $this->hasOne('App\Models\GameRankYearMonth', 'game_id', 'id');
    }

    public function eshopUSGame()
    {
        return $this->hasOne('App\Models\EshopUSGame', 'nsuid', 'eshop_europe_nsuid');
    }

    public function importRuleEshop()
    {
        return $this->hasOne('App\Models\GameImportRuleEshop', 'game_id', 'id');
    }

    public function dspNintendoCoUk()
    {
        return $this->hasMany('App\Models\DataSourceParsed', 'game_id', 'id')
            ->where('source_id', DataSource::DSID_NINTENDO_CO_UK);
    }

    public function isDigitalDelisted()
    {
        return $this->format_digital == self::FORMAT_DELISTED;
    }

    // Game status helpers

    public function isActive(): bool
    {
        return $this->game_status === GameStatus::ACTIVE;
    }

    public function isDelisted(): bool
    {
        return $this->game_status === GameStatus::DELISTED;
    }

    public function isSoftDeleted(): bool
    {
        return $this->game_status === GameStatus::SOFT_DELETED;
    }

    // Game status scopes

    public function scopeActive($query)
    {
        return $query->where('game_status', GameStatus::ACTIVE);
    }

    public function scopeDelisted($query)
    {
        return $query->where('game_status', GameStatus::DELISTED);
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

    // Searches

    public function scopeSearchTitle($query, $title)
    {
        if (is_array($title)) return false;
        if ($title) $query->where('title', 'like', '%'.$title.'%');
    }

    public function scopeSearchShowRankedUnranked($query, $showRankedUnranked)
    {
        if ($showRankedUnranked == 'Ranked') {
            $query->whereNotNull('game_rank');
        } elseif ($showRankedUnranked == 'Unranked') {
            $query->whereNull('game_rank');
        }
    }

    public function scopeSearchScoreMinimum($query, $scoreMinimum)
    {
        $scoreMinimum = (float) $scoreMinimum;
        if ($scoreMinimum) $query->where('rating_avg', '>=', $scoreMinimum)->whereNotNull('game_rank');
    }

    public function scopeSearchPriceMaximum($query, $priceMaximum)
    {
        $priceMaximum = (float) $priceMaximum;
        if ($priceMaximum) $query->where('price_eshop', '<=', $priceMaximum);
    }

    public function scopeSearchYearReleased($query, $yearReleased)
    {
        $yearReleased = (int) $yearReleased;
        if ($yearReleased) $query->where('release_year', $yearReleased);
    }

    public function scopeSearchCategoryId($query, $categoryIdList)
    {
        if ($categoryIdList && is_array($categoryIdList)) {
            $query->whereIn('category_id', $categoryIdList);
        }
    }

    public function scopeSearchSeriesId($query, $seriesId)
    {
        $seriesId = (int) $seriesId;
        if ($seriesId) $query->where('series_id', $seriesId);
    }

    public function scopeSearchCollectionId($query, $collectionId)
    {
        $collectionId = (int) $collectionId;
        if ($collectionId) $query->where('collection_id', $collectionId);
    }
}
