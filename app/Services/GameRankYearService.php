<?php


namespace App\Services;

use Illuminate\Support\Facades\DB;

use App\GameRankYear;


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
            ->leftJoin('game_primary_types', 'games.primary_type_id', '=', 'game_primary_types.id')
            ->select('games.*',
                'game_primary_types.primary_type',
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