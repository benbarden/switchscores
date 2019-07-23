<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * @var array
     */
    // is_admin has to be fillable or unit tests will fail
    protected $fillable = [
        'display_name', 'email', 'password', 'region', 'partner_id', 'is_admin',
        'twitter_user_id', 'twitter_name', 'login_date'
    ];

    /**
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'is_admin' => 'boolean',
    ];

    /**
     * @return bool
     */
    public function isAdmin()
    {
        return $this->is_admin == 1;
    }

    public function partner()
    {
        return $this->hasOne('App\Partner', 'id', 'partner_id');
    }
}
