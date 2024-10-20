<?php


namespace App\Services;

use App\Models\GameRankAllTime;
use Illuminate\Support\Facades\DB;


class GameRankAllTimeService
{
    /**
     * @return mixed
     */
    public function getTop($limit)
    {
        $gameRankList = GameRankAllTime::
            orderBy('game_rank', 'asc')
            ->limit($limit)
            ->get();
        return $gameRankList;
    }

    /**
     * @param $minRank
     * @param $maxRank
     * @param $customFilter
     * @return mixed
     */
    public function getList($minRank = null, $maxRank = null, $customFilter = null)
    {
        $games = DB::table('game_rank_alltime')
            ->join('games', 'game_rank_alltime.game_id', '=', 'games.id')
            ->leftJoin('categories', 'games.category_id', '=', 'categories.id')
            ->select('games.*',
                'categories.name AS category_name',
                'game_rank_alltime.game_rank');

        if ($customFilter == 'multiplayer') {
            $games = $games->where('games.players', '!=', '1');
        }
        if ($minRank && $maxRank) {
            $games = $games->where('game_rank_alltime.game_rank', '<=', $maxRank);
            $games = $games->where('game_rank_alltime.game_rank', '>=', $minRank);
        }

        $games = $games
            ->orderBy('game_rank_alltime.game_rank')
            ->orderBy('games.review_count', 'desc');

        $games = $games->get();
        return $games;
    }
}