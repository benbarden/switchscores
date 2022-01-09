<?php

namespace App\Domain\Role;

use App\Models\User;

/**
 * Class RoleChecker
 * @package App\Role
 */
class RoleChecker
{
    /**
     * @param \App\Models\User $user
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