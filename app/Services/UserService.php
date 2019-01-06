<?php


namespace App\Services;

use App\User;


class UserService
{
    /**
     * @param User $userData
     * @param $displayName
     * @param $email
     * @param $siteId
     * @return void
     */
    public function edit(
        User $userData, $displayName, $email, $siteId
    )
    {
        $values = [
            'display_name' => $displayName,
            'email' => $email,
            'site_id' => $siteId,
        ];

        $userData->fill($values);
        $userData->save();
    }

    public function find($id)
    {
        return User::find($id);
    }

    public function getAll()
    {
        return User::orderBy('created_at', 'desc')->get();
    }
}