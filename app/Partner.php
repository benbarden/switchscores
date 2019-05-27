<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    const TYPE_REVIEW_SITE = 1;
    const TYPE_GAMES_COMPANY = 2;

    const STATUS_PENDING = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 9;

    /**
     * @var string
     */
    protected $table = 'partners';

    /**
     * @var bool
     */
    public $timestamps = true;

    /**
     * @var array
     */
    protected $fillable = [
        'type_id', 'status', 'name', 'link_title', 'website_url', 'twitter_id',
        'feed_url', 'feed_url_prefix', 'rating_scale', 'allow_historic_content',
        'title_match_rule_pattern', 'title_match_index'
    ];

    public function isReviewSite()
    {
        return $this->type_id == self::TYPE_REVIEW_SITE;
    }

    public function isGamesCompany()
    {
        return $this->type_id == self::TYPE_GAMES_COMPANY;
    }

    public function allowHistoric()
    {
        return $this->allow_historic_content == 1;
    }

    public function links()
    {
        return $this->hasMany('App\ReviewLink', 'id', 'site_id');
    }
}
