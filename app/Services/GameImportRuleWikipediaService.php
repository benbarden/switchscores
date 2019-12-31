<?php


namespace App\Services;

use App\GameImportRuleWikipedia;

class GameImportRuleWikipediaService
{
    public function getByGameId($gameId)
    {
        return GameImportRuleWikipedia::where('game_id', $gameId)->first();
    }

    public function deleteByGameId($gameId)
    {
        GameImportRuleWikipedia::where('game_id', $gameId)->delete();
    }
}