<?php

namespace App\Domain\GameDeveloper;

use App\Models\GameDeveloper;

class Repository
{
    public function create($gameId, $developerId)
    {
        GameDeveloper::create([
            'game_id' => $gameId,
            'developer_id' => $developerId
        ]);
    }

    public function delete($gameDeveloperId)
    {
        GameDeveloper::where('id', $gameDeveloperId)->delete();
    }

    public function deleteByGameId($gameId)
    {
        GameDeveloper::where('game_id', $gameId)->delete();
    }

    public function find($id)
    {
        return GameDeveloper::find($id);
    }

    public function byGame($gameId)
    {
        $gameDevelopers = GameDeveloper::where('game_id', '=', $gameId)->get();

        $gameDevelopers = $gameDevelopers->sortBy(function($gameDev) {
            return $gameDev->developer->name;
        });

        return $gameDevelopers;
    }

    public function byDeveloperId($developerId)
    {
        return GameDeveloper::where('developer_id', $developerId)->get();
    }

    public function gameHasDeveloper($gameId, $developerId)
    {
        $gameDeveloper = GameDeveloper::where('game_id', $gameId)->where('developer_id', $developerId)->first();
        if ($gameDeveloper) {
            return true;
        } else {
            return false;
        }
    }
}