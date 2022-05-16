<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class ReviewSite extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    const STATUS_ACTIVE = 'Active';
    const STATUS_NO_RECENT_REVIEWS = 'No recent reviews';

    const REVIEW_IMPORT_BY_FEED = 'Feed';
    const REVIEW_IMPORT_BY_SCRAPER = 'Scraper';

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
        'review_code_regions', 'review_import_method'
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
}
