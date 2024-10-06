<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonalAccessToken extends Model
{
    const API_GET_GAMES = 'switch_get_games';

    /**
     * @var string
     */
    protected $table = 'personal_access_tokens';

    /**
     * @var array
     */
    protected $fillable = [
    ];

}
