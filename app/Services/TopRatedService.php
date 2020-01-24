<?php


namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class TopRatedService
{
    /**
     * @param $limit
     * @return mixed
     */
    public function getList($limit = null)
    {
        $games = DB::table('games')
            ->select('games.*')
            ->where('games.review_count', '>', '2')
            ->orderBy('games.rating_avg', 'desc')
            ->orderBy('games.review_count', 'desc');

        if ($limit != null) {
            $games = $games->limit($limit);
        }

        $games = $games->get();
        return $games;
    }

    /**
     * @return integer
     */
    public function getUnrankedCount()
    {
        $games = DB::table('games')
            ->select('games.*')
            ->where('games.eu_is_released', '=', '1')
            ->where('games.review_count', '<', '3')
            ->orderBy('games.rating_avg', 'desc');

        $topRatedCounter = $games->count();
        return $topRatedCounter;
    }

    /**
     * @param $year
     * @param $month
     * @return mixed
     */
    public function getByMonthWithRanks($year, $month)
    {
        $games = DB::table('games')
            ->select('games.*',
                'games.id AS game_id')
            ->whereYear('games.eu_release_date', '=', $year)
            ->whereMonth('games.eu_release_date', '=', $month)
            ->whereNotNull('games.game_rank')
            ->orderBy('games.rating_avg', 'desc');
        $games = $games->get();
        return $games;
    }
}