<?php


namespace App\Services;

use App\Models\GameRankYear;
use Illuminate\Support\Facades\DB;


class GameRankYearService
{
    /**
     * @return mixed
     */
    public function getByYear($year)
    {
        $gameRankList = GameRankYear::
            where('release_year', $year)
            ->orderBy('game_rank', 'asc')
            ->get();
        return $gameRankList;
    }

    /**
     * @param $year
     * @param $limit
     * @return mixed
     */
    public function getList($year, $limit = null)
    {
        $games = DB::table('game_rank_year')
            ->join('games', 'game_rank_year.game_id', '=', 'games.id')
            ->leftJoin('categories', 'games.category_id', '=', 'categories.id')
            ->select('games.*',
                'categories.name AS category_name',
                'game_rank_year.game_rank')
            ->where('game_rank_year.release_year', $year)
            ->orderBy('game_rank_year.game_rank')
            ->orderBy('games.review_count', 'desc');

        if ($limit != null) {
            $games = $games->limit($limit);
        }

        $games = $games->get();
        return $games;
    }

}