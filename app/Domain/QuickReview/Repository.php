<?php


namespace App\Domain\QuickReview;

use App\QuickReview;

class Repository
{
    public function byGameActive($gameId)
    {
        return QuickReview::where('game_id', $gameId)->where('item_status', QuickReview::STATUS_ACTIVE)->get();
    }
}