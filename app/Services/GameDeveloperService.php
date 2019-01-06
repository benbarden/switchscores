<?php


namespace App\Services;

use App\GameDeveloper;


class GameDeveloperService
{
    public function getByGameId($gameId)
    {
        return GameDeveloper::where('game_id', $gameId)->get();
    }

    public function getByDeveloperId($developerId)
    {
        return GameDeveloper::where('developer_id', $developerId)->get();
    }
}