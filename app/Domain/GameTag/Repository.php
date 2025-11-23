<?php


namespace App\Domain\GameTag;

use App\Models\GameTag;

class Repository
{
    public function create($gameId, $tagId)
    {
        GameTag::create([
            'game_id' => $gameId,
            'tag_id' => $tagId
        ]);
    }

    public function gameHasTag($gameId, $tagId)
    {
        $gameTagCount = GameTag::where('game_id', $gameId)->where('tag_id', $tagId)->count();
        return $gameTagCount > 0;
    }

    public function deleteGameTag($gameId, $tagId)
    {
        GameTag::where('game_id', $gameId)->where('tag_id', $tagId)->delete();
    }

    public function deleteByGameId($gameId)
    {
        GameTag::where('game_id', $gameId)->delete();
    }

    public function getGameTags($gameId)
    {
        return GameTag::where('game_id', $gameId)->get();
    }
}