<?php


namespace App\Domain\InviteCodeRequest;

use App\Models\InviteCodeRequest;

class Repository
{
    public function create($email, $bio, $status = InviteCodeRequest::STATUS_PENDING)
    {
        return InviteCodeRequest::create([
            'waitlist_email' => $email,
            'waitlist_bio' => $bio,
            'times_requested' => 1,
            'status' => $status,
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

    public function markAsSpam(InviteCodeRequest $inviteCodeRequest)
    {
        $inviteCodeRequest->status = InviteCodeRequest::STATUS_SPAM;
        $inviteCodeRequest->save();
    }

    public function getAll()
    {
        return InviteCodeRequest::orderBy('id', 'desc')->get();
    }

    public function getActive()
    {
        return InviteCodeRequest::where('status', '<>', InviteCodeRequest::STATUS_SPAM)->orderBy('id', 'desc')->get();
    }

    public function getSpam()
    {
        return InviteCodeRequest::where('status', InviteCodeRequest::STATUS_SPAM)->orderBy('id', 'desc')->get();
    }
}