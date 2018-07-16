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
    protected $fillable = [
        'display_name', 'email', 'password', 'is_admin', 'region',
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
}
