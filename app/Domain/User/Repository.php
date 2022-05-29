<?php


namespace App\Domain\User;

use App\Models\User;

class Repository
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

    public function edit(
        User $userData, $displayName, $email, $partnerId, $twitterUserId,
        $isStaff, $isDeveloper, $gamesCompanyId
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
            'games_company_id' => $gamesCompanyId,
        ];

        $userData->fill($values);
        $userData->save();
    }

    public function deleteUser($userId)
    {
        User::where('id', $userId)->delete();
    }

    public function setLastAccessDate(User $user, $todaysDate)
    {
        $user->last_access_date = $todaysDate;
        $user->save();
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

    public function getByTwitterId($twitterId)
    {
        return User::where('twitter_user_id', $twitterId)->first();
    }

    public function getCount()
    {
        return User::orderBy('created_at', 'desc')->count();
    }
}