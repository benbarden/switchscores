<?php


namespace App\Domain\GameCrawlLifecycle;

use App\Models\GameCrawlLifecycle;

class Repository
{
    public function deleteByGameId($gameId)
    {
        GameCrawlLifecycle::where('game_id', $gameId)->delete();
    }
}
