<?php


namespace App\Domain\QuickReview;

use App\Models\QuickReview;

class Repository
{
    public function find($id)
    {
        return QuickReview::find($id);
    }

    public function byGameActive($gameId)
    {
        return QuickReview::where('game_id', $gameId)->where('item_status', QuickReview::STATUS_ACTIVE)->get();
    }

    public function byUserGameIdList($userId)
    {
        return QuickReview::where('user_id', $userId)->orderBy('id', 'desc')->pluck('game_id');
    }
}