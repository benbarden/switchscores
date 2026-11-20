<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Another name a games company appears under, e.g. "Natsume Inc." for Natsume. A publisher
 * name that matches an alias links to the company.
 */
class GamesCompanyAlias extends Model
{
    protected $table = 'games_company_aliases';

    protected $fillable = ['games_company_id', 'name'];

    public function gamesCompany()
    {
        return $this->belongsTo(GamesCompany::class, 'games_company_id');
    }
}
