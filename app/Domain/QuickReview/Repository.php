<?php


namespace App\Domain\QuickReview;

use App\Models\QuickReview;

class Repository
{
    public function byGameActive($gameId)
    {
        return QuickReview::where('game_id', $gameId)->where('item_status', QuickReview::STATUS_ACTIVE)->get();
    }
}