<?php


namespace App\Services;

use Illuminate\Support\Facades\DB;

use App\GameRankYearMonth;


class GameRankYearMonthService
{
    /**
     * @return mixed
     */
    public function getByYearMonth($yearmonth)
    {
        $gameRankList = GameRankYearMonth::
            where('release_yearmonth', $yearmonth)
            ->orderBy('game_rank', 'asc')
            ->get();
        return $gameRankList;
    }

    /**
     * @param $yearmonth
     * @param $limit
     * @return mixed
     */
    public function getList($yearmonth, $limit = null)
    {
        $games = DB::table('game_rank_yearmonth')
            ->join('games', 'game_rank_yearmonth.game_id', '=', 'games.id')
            ->select('games.*',
                'game_rank_yearmonth.game_rank')
            ->where('game_rank_yearmonth.release_yearmonth', $yearmonth)
            ->orderBy('game_rank_yearmonth.game_rank')
            ->orderBy('games.review_count', 'desc');

        if ($limit != null) {
            $games = $games->limit($limit);
        }

        $games = $games->get();
        return $games;
    }

}