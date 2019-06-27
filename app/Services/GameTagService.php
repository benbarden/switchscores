<?php


namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\GameTag;


class GameTagService
{
    public function createTagGenreList(
        $gameId, $tagIdList
    )
    {
        if (count($tagIdList) == 0) return false;

        foreach ($tagIdList as $tagId) {
            $this->createGameTag($gameId, $tagId);
        }
    }

    public function createGameTag($gameId, $tagId)
    {
        GameTag::create([
            'game_id' => $gameId,
            'tag_id' => $tagId
        ]);
    }

    public function deleteGameTags(
        $gameId
    )
    {
        GameTag::where('game_id', $gameId)->delete();
    }

    public function delete($gameTagId)
    {
        GameTag::where('id', $gameTagId)->delete();
    }

    public function find($id)
    {
        return GameTag::find($id);
    }

    // ********************************************************** //

    public function getByGame($gameId)
    {
        return GameTag::where('game_id', $gameId)->get();
    }

    public function gameHasTag($gameId, $tagId)
    {
        $gameTag = GameTag::where('game_id', $gameId)
            ->where('tag_id', $tagId)
            ->first();
        if ($gameTag) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Helper method to get all of the tags that haven't been applied to the current game yet.
     * @param $gameId
     * @return mixed
     */
    public function getTagsNotOnGame($gameId)
    {
        $games = DB::select('
            select * from tags where id not in (select tag_id from game_tags where game_id = ?) ORDER BY tag_name
        ', [$gameId]);

        return $games;
    }
}