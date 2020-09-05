<?php


namespace App\Services;

use App\User;


class UserService
{
    public function createFromTwitterLogin(
        $twitterUserId, $twitterNickname
    )
    {
        $randomEmail = $twitterNickname.mt_rand(1000, 9999).'@switchscores.com';

        $values = [
            'display_name' => $twitterNickname,
            'email' => $randomEmail,
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
     * @param $isStaff
     * @param $isDeveloper
     */
    public function edit(
        User $userData, $displayName, $email, $partnerId, $twitterUserId, $isStaff, $isDeveloper
    )
    {
        $dbIsStaff     = $isStaff     == 'on' ? 1 : 0;
        $dbIsDeveloper = $isDeveloper == 'on' ? 1 : 0;

        $values = [
            'display_name' => $displayName,
            'email' => $email,
            'partner_id' => $partnerId,
            'twitter_user_id' => $twitterUserId,
            'is_staff' => $dbIsStaff,
            'is_developer' => $dbIsDeveloper,
        ];

        $userData->fill($values);
        $userData->save();
    }

    /**
     * @param $id
     * @return User
     */
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