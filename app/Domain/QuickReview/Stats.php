<?php

namespace App\Domain\QuickReview;

use App\Models\QuickReview;

class Stats
{
    /**
     * @return integer
     */
    public function totalByUser($userId)
    {
        return QuickReview::where('user_id', $userId)->count();
    }
}