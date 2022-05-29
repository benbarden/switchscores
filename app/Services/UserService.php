<?php


namespace App\Services;

use App\Models\User;

class UserService
{

    /**
     * @deprecated
     * @param $id
     * @return User
     */
    public function find($id)
    {
        return User::find($id);
    }

    public function getNewest()
    {
        return User::orderBy('created_at', 'desc')->first();
    }

    public function getMostPoints($limit = 10)
    {
        return User::orderBy('points_balance', 'desc')
            ->orderBy('last_access_date', 'desc')
            ->limit($limit)->get();
    }
}