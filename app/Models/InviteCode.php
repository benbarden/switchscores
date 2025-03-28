<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InviteCode extends Model
{
    /**
     * @var string
     */
    protected $table = 'invite_codes';

    /**
     * @var array
     */
    protected $fillable = [
        'invite_code', 'times_used', 'times_left', 'is_active',
        'games_company_id', 'reviewer_id'
    ];

    public function gamesCompany()
    {
        return $this->hasOne('App\Models\GamesCompany', 'id', 'games_company_id');
    }

    public function reviewer()
    {
        return $this->hasOne('App\Models\ReviewSite', 'id', 'reviewer_id');
    }

    public function partnerOutreach()
    {
        return $this->hasOne('App\Models\PartnerOutreach', 'invite_code_id', 'id');
    }
}
