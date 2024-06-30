<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GamesCompanySignup extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'games_company_signups';

    /**
     * @var array
     */
    protected $fillable = [
        'contact_name',
        'contact_role',
        'contact_email',
        'existing_company_id',
        'new_company_name',
        'new_company_type',
        'new_company_url',
        'new_company_twitter',
        'list_of_games',
    ];

    public function gamesCompany()
    {
        return $this->hasOne('App\Models\GamesCompany', 'id', 'existing_company_id');
    }
}
