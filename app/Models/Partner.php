<?php

namespace App\Models;

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

    /**
     * @var string
     */
    protected $table = 'partners';

    /**
     * @var array
     */
    protected $fillable = [
        'type_id', 'status', 'name', 'link_title', 'website_url', 'twitter_id',
        'rating_scale', 'review_count', 'last_review_date', 'last_outreach_id',
        'contact_name', 'contact_email', 'contact_form_link', 'review_code_regions',
        'review_import_method', 'is_low_quality'
    ];

    public function developerGames()
    {
        return $this->hasMany('App\Models\GameDeveloper', 'developer_id', 'id');
    }

    public function publisherGames()
    {
        return $this->hasMany('App\Models\GamePublisher', 'publisher_id', 'id');
    }

    public function user()
    {
        return $this->hasOne('App\Models\User', 'partner_id', 'id');
    }

    public function gamesCompanyUser()
    {
        return $this->hasOne('App\Models\User', 'games_company_id', 'id');
    }

    public function lastOutreach()
    {
        return $this->hasOne('App\Models\PartnerOutreach', 'id', 'last_outreach_id');
    }

    public function outreach()
    {
        return $this->hasMany('App\Models\PartnerOutreach', 'partner_id', 'id');
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
