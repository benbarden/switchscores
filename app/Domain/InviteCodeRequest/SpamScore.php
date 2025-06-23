<?php

namespace App\Domain\InviteCodeRequest;

use App\Models\InviteCodeRequest;

class SpamScore
{
    const SPAM_THRESHOLD = 3;

    private $score;

    private $request;

    public function __construct(
        InviteCodeRequest $request = null
    ){
        $this->request = $request;
        $this->score = 0;
    }

    public function getScore()
    {
        return $this->score;
    }

    public function updateScoreTimesRequested()
    {
        if (!$this->request) return false;

        $timesRequested = (int) $this->request->times_requested;

        $this->score += $timesRequested;
    }

    public function updateScoreBio()
    {
        if (!$this->request) return false;

        $bio = $this->request->waitlist_bio;

        if (!str_contains($bio, " ") && (strtoupper($bio) != 'GOOGLE')) {
            $this->score += 2;
        }
    }

    public function updateAll()
    {
        $this->updateScoreTimesRequested();
        $this->updateScoreBio();
    }

    public function isSpam()
    {
        return $this->score >= self::SPAM_THRESHOLD;
    }


}