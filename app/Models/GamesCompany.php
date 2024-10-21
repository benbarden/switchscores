<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class GamesCompany extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    /**
     * @var string
     */
    protected $table = 'games_companies';

    /**
     * @var array
     */
    protected $fillable = [
        'name', 'link_title', 'website_url', 'twitter_id', 'last_outreach_id', 'is_low_quality',
        'email', 'threads_id', 'bluesky_id'
    ];

    public function developerGames()
    {
        return $this->hasMany('App\Models\GameDeveloper', 'developer_id', 'id');
    }

    public function publisherGames()
    {
        return $this->hasMany('App\Models\GamePublisher', 'publisher_id', 'id');
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
}
