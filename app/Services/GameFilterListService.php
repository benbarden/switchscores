<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

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
     * @param $region
     * @param $tagId
     * @return mixed
     */
    public function getByTagWithDates($region, $tagId)
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
            ->orderBy('games.rating_avg', 'desc')
            ->orderBy('game_release_dates.release_date', 'desc');

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
    public function getGamesWithoutTypesOrTags()
    {
        $games = DB::table('games')
            ->leftJoin('game_tags', 'games.id', '=', 'game_tags.game_id')
            ->select('games.id', 'games.title', 'games.link_title', 'games.eshop_europe_fs_id', 'game_tags.tag_id')
            ->whereNull('game_tags.tag_id')
            ->whereNull('games.primary_type_id')
            ->orderBy('games.title', 'asc');

        $games = $games->get();
        return $games;
    }
}