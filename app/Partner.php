<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Partner extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    const TYPE_REVIEW_SITE = 1;
    const TYPE_GAMES_COMPANY = 2;

    const STATUS_PENDING = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 9;

    const SITE_WOS = 1;
    const SITE_SWITCH_PLAYER = 2;
    const SITE_NINTENDO_LIFE = 4;
    const SITE_GAMESPEW = 5;
    const SITE_NINTENDO_WORLD_REPORT = 8;
    const SITE_CUBED3 = 9;
    const SITE_VIDEO_CHUMS = 11;
    const SITE_GOD_IS_A_GEEK = 12;
    const SITE_PURE_NINTENDO = 13;
    const SITE_DIGITALLY_DOWNLOADED = 14;
    const SITE_DESTRUCTOID = 15;
    const SITE_NINTENDO_INSIDER = 17;
    const SITE_MIKETENDO64 = 18;
    const SITE_NINDIE_SPOTLIGHT = 19;
    const SITE_THE_SWITCH_EFFECT = 20;
    const SITE_100_HOUR_REVIEWS = 21;
    const SITE_THE_NEW_ODYSSEY = 22;
    const SITE_SWITCHWATCH = 23;
    const SITE_THE_NINTENDO_NOMAD = 24;
    const SITE_TWO_BEARD_GAMING = 25;
    const SITE_SWITCH_INDIE_FIX = 26;
    const SITE_SIDEQUEST_VGM = 27;
    const SITE_NINTENDAD = 28;
    const SITE_RAPID_REVIEWS_UK = 29;
    const SITE_JPS_SWITCHMANIA = 30;
    const SITE_GERT_LUSH_GAMING = 31;
    const SITE_SWITCH_ATLANTIC = 32;

    /**
     * @var string
     */
    protected $table = 'partners';

    /**
     * @var array
     */
    protected $fillable = [
        'type_id', 'status', 'name', 'link_title', 'website_url', 'twitter_id',
        'feed_url', 'feed_url_prefix', 'rating_scale', 'allow_historic_content',
        'title_match_rule_pattern', 'title_match_index',
        'review_count', 'last_review_date', 'last_outreach_id',
        'contact_name', 'contact_email', 'contact_form_link', 'review_code_regions',
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
        return $this->hasMany('App\ReviewLink', 'site_id', 'id');
    }

    public function developerGames()
    {
        return $this->hasMany('App\GameDeveloper', 'developer_id', 'id');
    }

    public function publisherGames()
    {
        return $this->hasMany('App\GamePublisher', 'publisher_id', 'id');
    }

    public function user()
    {
        return $this->hasOne('App\User', 'partner_id', 'id');
    }

    public function lastOutreach()
    {
        return $this->hasOne('App\PartnerOutreach', 'id', 'last_outreach_id');
    }

    public function outreach()
    {
        return $this->hasMany('App\PartnerOutreach', 'partner_id', 'id');
    }

    public function isLastReviewHistoric()
    {
        if ($this->last_review_date == null) {
            return true;
        } elseif (date('Y-m-d', strtotime('-30 days')) > $this->last_review_date) {
            // If the review date is older than 30 days from today, it's history!
            return true;
        } else {
            return false;
        }
    }
}
