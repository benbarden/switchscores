<?php


namespace App\Services;

use App\GameImportRuleEshop;

class GameImportRuleEshopService
{
    public function getByGameId($gameId)
    {
        return GameImportRuleEshop::where('game_id', $gameId)->first();
    }

    public function deleteByGameId($gameId)
    {
        GameImportRuleEshop::where('game_id', $gameId)->delete();
    }
}