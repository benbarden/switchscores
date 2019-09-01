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
    // is_admin, is_owner, is_staff: these have to be fillable or unit tests will fail
    protected $fillable = [
        'is_admin', 'is_owner', 'is_staff', 'user_roles',
        'display_name', 'email', 'password', 'region', 'partner_id',
        'twitter_user_id', 'twitter_name', 'login_date', 'points_balance'
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
        'is_owner' => 'boolean',
        'is_staff' => 'boolean',
        'user_roles' => 'array',
    ];

    /**
     * @return bool
     */
    public function isOwner()
    {
        return $this->is_owner == 1;
    }

    /**
     * @deprecated
     * @return bool
     */
    public function isAdmin()
    {
        return $this->is_admin == 1;
    }

    /**
     * @return bool
     */
    public function isStaff()
    {
        return $this->is_staff == 1;
    }

    public function partner()
    {
        return $this->hasOne('App\Partner', 'id', 'partner_id');
    }

    public function pointsTransactions()
    {
        return $this->hasMany('App\UserPointTransaction', 'user_id', 'id');
    }

    // Roles

    /***
     * @param string $role
     * @return $this
     */
    public function addRole(string $role)
    {
        $roles = $this->getRoles();
        $roles[] = $role;

        $roles = array_unique($roles);
        $this->setRoles($roles);

        return $this;
    }

    /**
     * @param array $roles
     * @return $this
     */
    public function setRoles(array $roles)
    {
        $this->setAttribute('user_roles', $roles);
        return $this;
    }

    /***
     * @param $role
     * @return mixed
     */
    public function hasRole($role)
    {
        return in_array($role, $this->getRoles());
    }

    /***
     * @param $roles
     * @return mixed
     */
    public function hasRoles($roles)
    {
        $currentRoles = $this->getRoles();
        foreach ($roles as $role) {
            if (!in_array($role, $currentRoles)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        $roles = $this->getAttribute('user_roles');

        if (is_null($roles)) {
            $roles = [];
        }

        return $roles;
    }
}
