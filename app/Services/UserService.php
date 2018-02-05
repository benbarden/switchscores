<?php


namespace App\Services;

use App\User;


class UserService
{
    public function find($id)
    {
        return User::find($id);
    }

    public function getAll()
    {
        return User::orderBy('created_at', 'desc')->get();
    }
}