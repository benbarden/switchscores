<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

use App\Game;

class GameFilterListService
{
    /**
     * @param $tagId
     * @return mixed
     */
    public function getByTag($tagId)
    {
        $games = DB::table('games')
            ->join('game_tags', 'games.id', '=', 'game_tags.game_id')
            ->join('tags', 'game_tags.tag_id', '=', 'tags.id')
            ->select('games.*',
                'game_tags.tag_id',
                'games.id AS game_id',
                'game_tags.id AS game_tag_id',
                'tags.tag_name')
            ->where('game_tags.tag_id', $tagId)
            ->orderBy('games.id', 'desc');

        $games = $games->get();
        return $games;
    }

    /**
     * @param $tagId
     * @return mixed
     */
    public function getByTagWithDates($tagId)
    {
        $games = DB::table('games')
            ->join('game_tags', 'games.id', '=', 'game_tags.game_id')
            ->join('tags', 'game_tags.tag_id', '=', 'tags.id')
            ->select('games.*',
                'game_tags.tag_id',
                'tags.tag_name')
            ->where('game_tags.tag_id', $tagId)
            ->where('games.eu_is_released', '1')
            ->orderBy('games.rating_avg', 'desc')
            ->orderBy('games.eu_release_date', 'desc');

        $games = $games->get();
        return $games;
    }

    /**
     * @return mixed
     */
    public function getGamesWithoutTags()
    {
        $games = DB::table('games')
            ->leftJoin('game_tags', 'games.id', '=', 'game_tags.game_id')
            ->select('games.id', 'games.title', 'games.link_title', 'games.eshop_europe_fs_id', 'game_tags.tag_id')
            ->whereNull('game_tags.tag_id')
            ->orderBy('games.title', 'asc');

        $games = $games->get();
        return $games;
    }

    /**
     * @return mixed
     */
    public function getGamesWithoutCategoriesOrTags()
    {
        $games = DB::table('games')
            ->leftJoin('game_tags', 'games.id', '=', 'game_tags.game_id')
            ->select('games.id', 'games.title', 'games.link_title', 'games.eshop_europe_fs_id', 'game_tags.tag_id')
            ->whereNull('game_tags.tag_id')
            ->whereNull('games.category_id')
            ->orderBy('games.title', 'asc');

        $games = $games->get();
        return $games;
    }

    public function getGamesWithoutEshopEuropeFsId()
    {
        $games = DB::table('games')
            ->select('games.*')
            ->whereNull('games.eshop_europe_fs_id')
            ->orderBy('games.id', 'desc');

        $games = $games->get();
        return $games;
        //return Game::whereNull('eshop_europe_fs_id')->orderBy('games.id', 'desc');
    }

    /**
     * @param $genreId
     * @return mixed
     */
    public function getGamesByGenre($genreId)
    {
        // First get a list of the game IDs that have this genre
        $games = Game::with('gameGenres')
            ->whereHas('gameGenres', function($query) use ($genreId) {
                $query->where('game_genres.genre_id', '=', $genreId);
            })
            ->orderBy('games.title', 'asc');
        return $games->get();
    }
}