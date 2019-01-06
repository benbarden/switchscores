<?php


namespace App\Services;

use App\GamePublisher;


class GamePublisherService
{
    public function getByGameId($gameId)
    {
        return GamePublisher::where('game_id', $gameId)->get();
    }

    public function getByPublisherId($publisherId)
    {
        return GamePublisher::where('publisher_id', $publisherId)->get();
    }
}