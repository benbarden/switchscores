<?php


namespace App\Services;

use App\Models\Game;
use Illuminate\Support\Facades\DB;

class TopRatedService
{
    /**
     * @return integer
     */
    public function getUnrankedCount()
    {
        $games = DB::table('games')
            ->select('games.*')
            ->where('games.eu_is_released', '=', '1')
            ->where('games.review_count', '<', '3')
            ->where('format_digital', '<>', Game::FORMAT_DELISTED)
            ->orderBy('games.rating_avg', 'desc');

        $topRatedCounter = $games->count();
        return $topRatedCounter;
    }

    public function getUnrankedCountByReviewCount($reviewCount)
    {
        $gamesCount = Game::where('eu_is_released', 1)
            ->where('review_count', $reviewCount)
            ->where('format_digital', '<>', Game::FORMAT_DELISTED)
            ->count();

        return $gamesCount;
    }

    public function getUnrankedListByReviewCount($reviewCount, $limit = null)
    {
        $gamesList = Game::where('eu_is_released', 1)
            ->where('review_count', $reviewCount)
            ->where('format_digital', '<>', Game::FORMAT_DELISTED)
            ->orderBy('rating_avg', 'desc')
            ->orderBy('eu_release_date', 'desc');

        if ($limit) {
            $gamesList = $gamesList->limit($limit);
        }

        return $gamesList->get();
    }

    public function getUnrankedCountByReleaseYear($releaseYear)
    {
        $gamesCount = Game::where('eu_is_released', 1)
            ->where('release_year', $releaseYear)
            ->where('review_count', '<', '3')
            ->where('format_digital', '<>', Game::FORMAT_DELISTED)
            ->count();

        return $gamesCount;
    }

    public function getUnrankedListByReleaseYear($releaseYear, $limit = null)
    {
        $gamesList = Game::where('eu_is_released', 1)
            ->where('release_year', $releaseYear)
            ->where('review_count', '<', '3')
            ->where('format_digital', '<>', Game::FORMAT_DELISTED)
            ->orderBy('rating_avg', 'desc')
            ->orderBy('eu_release_date', 'desc');

        if ($limit) {
            $gamesList = $gamesList->limit($limit);
        }

        return $gamesList->get();
    }

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