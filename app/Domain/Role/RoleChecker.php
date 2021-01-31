<?php

namespace App\Domain\Role;

use App\User;

/**
 * Class RoleChecker
 * @package App\Role
 */
class RoleChecker
{
    /**
     * @param User $user
     * @param string $role
     * @return bool
     */
    public function check(User $user, string $role)
    {
        // Owner has all access
        if ($user->isOwner()) {
            return true;
        }

        return $user->hasRole($role);
    }
}