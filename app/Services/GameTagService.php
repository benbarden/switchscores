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
     */
    public function getTagsNotOnGame($gameId)
    {
        $games = DB::select('
            select * from tags where id not in (select tag_id from game_tags where game_id = ?) ORDER BY tag_name
        ', [$gameId]);

        return $games;
    }

    /**
     * @param $region
     * @param $tagId
     * @return mixed
     */
    public function getGamesByTag($region, $tagId)
    {
        $games = DB::table('games')
            ->join('game_release_dates', 'games.id', '=', 'game_release_dates.game_id')
            ->join('game_tags', 'games.id', '=', 'game_tags.game_id')
            ->join('tags', 'game_tags.tag_id', '=', 'tags.id')
            ->select('games.*',
                'game_release_dates.release_date',
                'game_release_dates.is_released',
                'game_release_dates.upcoming_date',
                'game_release_dates.release_year',
                'game_tags.tag_id',
                'tags.tag_name')
            ->where('game_tags.tag_id', $tagId)
            ->where('game_release_dates.region', $region)
            ->where('game_release_dates.is_released', '1')
            ->orderBy('games.title', 'asc');

        $games = $games->get();
        return $games;
    }

    /**
     * @param $region
     * @return mixed
     */
    public function getGamesWithoutTags($region)
    {
        $games = DB::table('games')
            ->leftJoin('game_tags', 'games.id', '=', 'game_tags.game_id')
            ->join('game_release_dates', 'games.id', '=', 'game_release_dates.game_id')
            ->select('games.id', 'games.title', 'games.link_title',
                'game_release_dates.release_date',
                'game_release_dates.is_released',
                'game_release_dates.upcoming_date',
                'game_release_dates.release_year',
                'game_genres.genre_id')
            ->where('game_release_dates.region', $region)
            ->where('game_release_dates.is_released', '1')
            ->whereNull('game_tags.tag_id')
            ->orderBy('games.title', 'asc');

        $games = $games->get();
        return $games;
    }
}