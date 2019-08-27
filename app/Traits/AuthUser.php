<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

use App\Services\UserService;
use App\User;

trait AuthUser
{
    /**
     * @param UserService $userService
     * @return User
     */
    public function getValidUser(UserService $userService)
    {
        $userId = Auth::id();

        $user = $userService->find($userId);

        return $user;
    }
}