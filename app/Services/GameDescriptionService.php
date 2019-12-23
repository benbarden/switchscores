<?php


namespace App\Services;

use App\GameDescription;

class GameDescriptionService
{
    public function deleteByGameId($gameId)
    {
        GameDescription::where('game_id', $gameId)->delete();
    }

    public function find($id)
    {
        return GameDescription::find($id);
    }

    public function getActiveByGame($gameId)
    {
        return GameDescription::where('game_id', $gameId)
            ->where('status', GameDescription::STATUS_PENDING)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}