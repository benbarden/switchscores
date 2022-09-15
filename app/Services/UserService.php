<?php


namespace App\Services;

use App\Models\User;

class UserService
{
    public function find($id)
    {
        return User::find($id);
    }
}