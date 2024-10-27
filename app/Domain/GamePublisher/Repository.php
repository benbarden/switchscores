<?php

namespace App\Domain\GamePublisher;

use App\Models\GamePublisher;

class Repository
{
    public function create($gameId, $publisherId)
    {
        GamePublisher::create([
            'game_id' => $gameId,
            'publisher_id' => $publisherId
        ]);
    }

    public function delete($gamePublisherId)
    {
        GamePublisher::where('id', $gamePublisherId)->delete();
    }

    public function deleteByGameId($gameId)
    {
        GamePublisher::where('game_id', $gameId)->delete();
    }

    public function find($id)
    {
        return GamePublisher::find($id);
    }

    public function byGame($gameId)
    {
        $gamePublishers = GamePublisher::where('game_id', '=', $gameId)->get();

        $gamePublishers = $gamePublishers->sortBy(function($gamePub) {
            return $gamePub->publisher->name;
        });

        return $gamePublishers;
    }

    public function byPublisherId($publisherId)
    {
        return GamePublisher::where('publisher_id', $publisherId)->get();
    }

    public function gameHasPublisher($gameId, $publisherId)
    {
        $gamePublisher = GamePublisher::where('game_id', $gameId)->where('publisher_id', $publisherId)->first();
        if ($gamePublisher) {
            return true;
        } else {
            return false;
        }
    }
}