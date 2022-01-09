<?php


namespace App\Services;

use App\Models\User;

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

    public function setLastAccessDate(User $user, $todaysDate)
    {
        $user->last_access_date = $todaysDate;
        $user->save();
    }

    /**
     * @deprecated
     * @param $id
     * @return User
     */
    public function find($id)
    {
        return User::find($id);
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