<?php


namespace App\Domain\InviteCode;

use App\Models\InviteCode;

class Repository
{
    public function create($code, $timesUsed, $timesLeft, $isActive, $gamesCompanyId, $reviewerId)
    {
        InviteCode::create([
            'invite_code' => $code,
            'times_used' => $timesUsed,
            'times_left' => $timesLeft,
            'is_active' => $isActive,
            'games_company_id' => $gamesCompanyId,
            'reviewer_id' => $reviewerId,
        ]);
    }

    public function edit(InviteCode $inviteCode, $code, $timesUsed, $timesLeft, $isActive, $gamesCompanyId, $reviewerId)
    {
        $values = [
            'invite_code' => $code,
            'times_used' => $timesUsed,
            'times_left' => $timesLeft,
            'is_active' => $isActive,
            'games_company_id' => $gamesCompanyId,
            'reviewer_id' => $reviewerId,
        ];

        $inviteCode->fill($values);
        $inviteCode->save();
    }

    public function delete($id)
    {
        InviteCode::where('id', $id)->delete();
    }

    public function find($id)
    {
        return InviteCode::find($id);
    }

    public function getAll()
    {
        return InviteCode::orderBy('invite_code', 'asc')->get();
    }

    public function getByCode($code)
    {
        return InviteCode::where('invite_code', $code)->first();
    }
}