<?php


namespace App\Services;

use Illuminate\Support\Facades\DB;

use App\GameRankAllTime;


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

    public function countRanked()
    {
        return GameRankAllTime::orderBy('game_rank', 'asc')->count();
    }

    /**
     * @param $maxRank
     * @return mixed
     */
    public function getList($maxRank = null)
    {
        $games = DB::table('game_rank_alltime')
            ->join('games', 'game_rank_alltime.game_id', '=', 'games.id')
            ->select('games.*',
                'game_rank_alltime.game_rank');

        if ($maxRank) {
            $games = $games->where('game_rank_alltime.game_rank', '<=', $maxRank);
        }

        $games = $games
            ->orderBy('game_rank_alltime.game_rank')
            ->orderBy('games.review_count', 'desc');

        $games = $games->get();
        return $games;
    }

}