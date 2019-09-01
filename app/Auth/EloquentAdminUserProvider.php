<?php

namespace App\Auth;

use Illuminate\Auth\EloquentUserProvider;
use App\User;

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