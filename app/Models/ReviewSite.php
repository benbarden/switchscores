<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class ReviewSite extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    const STATUS_ACTIVE = 'Active';
    const STATUS_NO_RECENT_REVIEWS = 'No recent reviews';
    const STATUS_ARCHIVED = 'Archived';

    const REVIEW_IMPORT_BY_FEED = 'Feed';
    const REVIEW_IMPORT_BY_SCRAPER = 'Scraper';

    const SITE_SWITCH_SCORES = 1;
    const SITE_SWITCH_PLAYER = 2;
    const SITE_NINTENDO_LIFE = 4;
    const SITE_NINTENDO_WORLD_REPORT = 8;

    const SITE_GOD_IS_A_GEEK = 12;
    const SITE_PURE_NINTENDO = 13;
    const SITE_NINTENPEDIA = 621;
    const SITE_HEY_POOR_PLAYER = 626;
    const SITE_SWITCHABOO = 2109;
    const SITE_POCKET_TACTICS = 2593;

    const SITE_PS3BLOG_NET = 2459;
    const SITE_PS4BLOG_NET = 2361;

    /**
     * @var string
     */
    protected $table = 'review_sites';

    /**
     * @var array
     */
    protected $fillable = [
        'status', 'name', 'link_title', 'website_url', 'twitter_id',
        'rating_scale', 'review_count', 'last_review_date',
        'contact_name', 'contact_email', 'contact_form_link',
        'review_code_regions', 'review_import_method', 'disable_links'
    ];

    public function links()
    {
        return $this->hasMany('App\Models\ReviewLink', 'site_id', 'id');
    }

    public function feedLinks()
    {
        return $this->hasMany('App\Models\PartnerFeedLink', 'site_id', 'id');
    }

    public function user()
    {
        return $this->hasOne('App\Models\User', 'partner_id', 'id');
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

    public function isYoutubeChannel()
    {
        $youtubeBaseLink = 'https://youtube.com/';
        if (substr($this->website_url, 0, strlen('https://youtube.com/')) == $youtubeBaseLink) {
            return true;
        } else {
            return false;
        }
    }
}
