<?php

namespace App\Domain\TopRated;

use App\Models\Game;
use Illuminate\Support\Facades\DB;

class DbQueries
{
    /**
     * @param $maxRank
     * @param $mode
     * @return mixed
     */
    public function getList($minRank = null, $maxRank = null, $mode = null)
    {
        $games = DB::table('game_rank_alltime')
            ->join('games', 'game_rank_alltime.game_id', '=', 'games.id')
            ->leftJoin('categories', 'games.category_id', '=', 'categories.id')
            ->select('games.*', 'games.id AS game_id',
                'categories.name AS category_name',
                'game_rank_alltime.game_rank');

        if ($minRank && $maxRank) {
            $games = $games->where('game_rank_alltime.game_rank', '<=', $maxRank);
            $games = $games->where('game_rank_alltime.game_rank', '>=', $minRank);
        }

        if ($mode == 'random-one') {
            $games = $games->orderBy(DB::raw('RAND()'))->limit(1);
            return $games->first();
        } else {
            $games = $games
                ->orderBy('game_rank_alltime.game_rank')
                ->orderBy('games.review_count', 'desc');
            return $games->get();
        }
    }

    public function getRandomFromTop100()
    {
        return $this->getList(1, 100, 'random-one'); //->inRandomOrder()->first();
    }

    public function byYear($year, $limit = null)
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

    public function byYearMonth($yearmonth, $limit = null)
    {
        $games = DB::table('game_rank_yearmonth')
            ->join('games', 'game_rank_yearmonth.game_id', '=', 'games.id')
            ->leftJoin('categories', 'games.category_id', '=', 'categories.id')
            ->select('games.*',
                'game_rank_yearmonth.game_rank',
                'categories.name AS category_name')
            ->where('game_rank_yearmonth.release_yearmonth', $yearmonth)
            ->orderBy('game_rank_yearmonth.game_rank')
            ->orderBy('games.review_count', 'desc');

        if ($limit != null) {
            $games = $games->limit($limit);
        }

        $games = $games->get();
        return $games;
    }

    public function byMonthWithRanks($consoleId, $year, $month, $limit = null)
    {
        $games = DB::table('games')
            ->select('games.*',
                'games.id AS game_id')
            ->where('games.console_id', $consoleId)
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

    public function byMonthUnranked($year, $month, $limit = null)
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

    public function rankedCountByConsoleAndYear($consoleId, $year)
    {
        $rankCount = DB::select('
            SELECT
            CASE
            WHEN game_rank IS NULL THEN "Unranked"
            ELSE "Ranked"
            END AS is_ranked,
            count(*) AS count
            FROM games
            WHERE console_id = ?
            AND release_year = ?
            AND format_digital <> ?
            GROUP BY is_ranked
        ', [$consoleId, $year, Game::FORMAT_DELISTED]);

        return $rankCount;
    }

    /**
     * @deprecated
     * @param $year
     * @return array
     */
    public function rankedCountByYear($year)
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
}