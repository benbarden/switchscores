<?php


namespace App\Domain\User;

use App\User;

class Repository
{
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
}