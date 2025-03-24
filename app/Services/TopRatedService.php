<?php


namespace App\Services;

use App\Models\Game;
use Illuminate\Support\Facades\DB;

class TopRatedService
{

    /**
     * $param $year
     * @return integer
     */
    public function getRankedCountByYear($year)
    {
        $rankCount = DB::select('
            SELECT
            CASE
            WHEN game_rank IS NULL THEN "Unranked"
            ELSE "Ranked"
            END AS is_ranked,
            count(*) AS count
            FROM games
            WHERE release_year = ?
            AND format_digital <> ?
            GROUP BY is_ranked
        ', [$year, Game::FORMAT_DELISTED]);

        return $rankCount;
    }

    /**
     * @param $year
     * @param $month
     * @param $limit
     * @return mixed
     */
    public function getByMonthWithRanks($year, $month, $limit = null)
    {
        $games = DB::table('games')
            ->select('games.*',
                'games.id AS game_id')
            ->whereYear('games.eu_release_date', '=', $year)
            ->whereMonth('games.eu_release_date', '=', $month)
            ->whereNotNull('games.game_rank')
            ->orderBy('games.rating_avg', 'desc');
        if ($limit) {
            $games = $games->limit($limit);
        }
        $games = $games->get();
        return $games;
    }

    /**
     * @param $year
     * @param $month
     * @param $limit
     * @return mixed
     */
    public function getByMonthUnranked($year, $month, $limit = null)
    {
        $games = DB::table('games')
            ->select('games.*',
                'games.id AS game_id')
            ->whereYear('games.eu_release_date', '=', $year)
            ->whereMonth('games.eu_release_date', '=', $month)
            ->whereNull('games.game_rank')
            ->orderBy('games.review_count', 'desc');
        if ($limit) {
            $games = $games->limit($limit);
        }
        $games = $games->get();
        return $games;
    }
}