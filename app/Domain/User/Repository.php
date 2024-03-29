<?php


namespace App\Domain\User;

use App\Models\User;

use Illuminate\Support\Facades\Auth;

class Repository
{
    /**
     * @param $id
     * @return User
     */
    public function find($id)
    {
        return User::find($id);
    }

    public function currentUser()
    {
        $userId = Auth::id();
        if (!$userId) return null;

        $user = $this->find($userId);
        return $user;
    }

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