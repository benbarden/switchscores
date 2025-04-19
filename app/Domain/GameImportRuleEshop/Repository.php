<?php

namespace App\Domain\GameImportRuleEshop;

use App\Models\GameImportRuleEshop;

class Repository
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