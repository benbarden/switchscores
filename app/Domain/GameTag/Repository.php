<?php


namespace App\Domain\GameTag;

use App\GameTag;

class Repository
{
    public function create($gameId, $tagId)
    {
        GameTag::create([
            'game_id' => $gameId,
            'tag_id' => $tagId
        ]);
    }

    public function deleteAllForGame($gameId)
    {
        GameTag::where('game_id', $gameId)->delete();
    }

    public function getGameTags($gameId)
    {
        return GameTag::where('game_id', $gameId)->get();
    }
}