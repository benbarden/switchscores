<?php


namespace App\Services;

use App\User;


class UserService
{
    public function createFromTwitterLogin(
        $twitterUserId, $twitterNickname
    )
    {
        $values = [
            'display_name' => $twitterNickname,
            'twitter_user_id' => $twitterUserId,
            'twitter_name' => $twitterNickname,
            'region' => 'eu',
        ];
        $user = User::create($values);
        return $user;
    }

    /**
     * @param User $userData
     * @param $displayName
     * @param $email
     * @param $partnerId
     * @param $twitterUserId
     */
    public function edit(
        User $userData, $displayName, $email, $partnerId, $twitterUserId
    )
    {
        $values = [
            'display_name' => $displayName,
            'email' => $email,
            'partner_id' => $partnerId,
            'twitter_user_id' => $twitterUserId,
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

    public function getByTwitterId($twitterId)
    {
        return User::where('twitter_user_id', $twitterId)->first();
    }
}