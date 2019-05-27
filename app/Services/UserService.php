<?php


namespace App\Services;

use App\User;


class UserService
{
    /**
     * @param User $userData
     * @param $displayName
     * @param $email
     * @param $partnerId
     * @return void
     */
    public function edit(
        User $userData, $displayName, $email, $partnerId
    )
    {
        $values = [
            'display_name' => $displayName,
            'email' => $email,
            'partner_id' => $partnerId,
        ];

        $userData->fill($values);
        $userData->save();
    }

    public function find($id)
    {
        return User::find($id);
    }

    public function deleteUser($userId)
    {
        User::where('id', $userId)->delete();
    }

    public function getAll()
    {
        return User::orderBy('created_at', 'desc')->get();
    }

    public function getCount()
    {
        return User::orderBy('created_at', 'desc')->count();
    }
}