<?php

namespace App\Auth;

use App\Models\User;
use Illuminate\Auth\EloquentUserProvider;

class EloquentAdminUserProvider extends EloquentUserProvider
{
    public function retrieveByCredentials(array $credentials)
    {
        /** @var $user User */
        $user = parent::retrieveByCredentials($credentials);

        if ($user && $user->isOwner()) {
            return $user;
        } else {
            return null;
        }
    }
}