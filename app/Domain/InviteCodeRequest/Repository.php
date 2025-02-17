<?php


namespace App\Domain\InviteCodeRequest;

use App\Models\InviteCodeRequest;

class Repository
{
    public function create($email, $bio)
    {
        return InviteCodeRequest::create([
            'waitlist_email' => $email,
            'waitlist_bio' => $bio,
            'times_requested' => 1,
        ]);
    }

    public function getByEmail($email)
    {
        return InviteCodeRequest::where('waitlist_email', $email)->first();
    }

    public function incrementTimesRequested(InviteCodeRequest $inviteCodeRequest)
    {
        $inviteCodeRequest->times_requested++;
        $inviteCodeRequest->save();
    }

    public function getAll()
    {
        return InviteCodeRequest::orderBy('id', 'desc')->get();
    }
}