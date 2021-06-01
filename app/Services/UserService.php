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
     * @param $isGamesCompany
     */
    public function edit(
        User $userData, $displayName, $email, $partnerId, $twitterUserId,
        $isStaff, $isDeveloper, $isGamesCompany
    )
    {
        $dbIsStaff     = $isStaff     == 'on' ? 1 : 0;
        $dbIsDeveloper = $isDeveloper == 'on' ? 1 : 0;
        $dbIsGamesCompany = $isGamesCompany == 'on' ? 1 : 0;

        $values = [
            'display_name' => $displayName,
            'email' => $email,
            'partner_id' => $partnerId,
            'twitter_user_id' => $twitterUserId,
            'is_staff' => $dbIsStaff,
            'is_developer' => $dbIsDeveloper,
            'is_games_company' => $dbIsGamesCompany,
        ];

        $userData->fill($values);
        $userData->save();
    }

    public function setLastAccessDate(User $user, $todaysDate)
    {
        $user->last_access_date = $todaysDate;
        $user->save();
    }

    public function deleteUser($userId)
    {
        User::where('id', $userId)->delete();
    }

    /**
     * @param $id
     * @return User
     */
    public function find($id)
    {
        return User::find($id);
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