<?php


namespace App\Domain\InviteCode;

use App\InviteCode;

class CodeRedemption
{
    private $inviteCode;

    public function __construct(InviteCode $inviteCode)
    {
        $this->inviteCode = $inviteCode;
    }

    public function redeemOnce()
    {
        $timesUsed = $this->inviteCode->times_used;
        $timesLeft = $this->inviteCode->times_left;
        $isActive = $this->inviteCode->is_active;

        if ($timesLeft == 0) {
            throw new \Exception('Cannot redeem code: no uses left');
        }

        if ($isActive == 0) {
            throw new \Exception('Cannot redeem code: not active');
        }

        // Update values
        $timesUsed++;
        $timesLeft--;
        if ($timesLeft == 0) $isActive = 0;

        $this->inviteCode->times_used = $timesUsed;
        $this->inviteCode->times_left = $timesLeft;
        $this->inviteCode->is_active = $isActive;
        $this->inviteCode->save();
    }
}