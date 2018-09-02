<?php


namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class TopRatedService
{
    /**
     * @param $region
     * @param $limit
     * @return mixed
     */
    public function getList($region, $limit = null)
    {
        $games = DB::table('games')
            ->join('game_release_dates', 'games.id', '=', 'game_release_dates.game_id')
            ->select('games.*',
                'game_release_dates.release_date',
                'game_release_dates.is_released',
                'game_release_dates.upcoming_date',
                'game_release_dates.release_year')
            ->where('game_release_dates.region', $region)
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
     * @param $region
     * @param $year
     * @param $limit
     * @return \Illuminate\Support\Collection
     */
    public function getByYear($region, $year, $limit = null)
    {
        $games = DB::table('games')
            ->join('game_release_dates', 'games.id', '=', 'game_release_dates.game_id')
            ->select('games.*',
                'game_release_dates.release_date',
                'game_release_dates.is_released',
                'game_release_dates.upcoming_date',
                'game_release_dates.release_year')
            ->where('game_release_dates.region', $region)
            ->where('game_release_dates.release_year', $year)
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
     * @param $region
     * @param $days
     * @param int $limit
     * @return mixed
     */
    public function getLastXDays($region, $days, $limit = 10)
    {
        $days = (int) $days;

        $games = DB::table('games')
            ->join('game_release_dates', 'games.id', '=', 'game_release_dates.game_id')
            ->select('games.*',
                'game_release_dates.release_date',
                'game_release_dates.is_released',
                'game_release_dates.upcoming_date',
                'game_release_dates.release_year')
            ->where('game_release_dates.region', $region)
            ->where('games.review_count', '>', '2')
            ->whereBetween('release_date', array(Carbon::now()->subDays($days), Carbon::now()->addDay()))
            ->orderBy('games.rating_avg', 'desc')
            ->orderBy('games.review_count', 'desc')
            ->limit($limit);

        $games = $games->get();
        return $games;
    }

    /**
     * Top Rated - All-time
     * Just a counter. Used on Game pages
     * @param $region
     * @return integer
     */
    public function getCount($region)
    {
        $games = DB::table('games')
            ->join('game_release_dates', 'games.id', '=', 'game_release_dates.game_id')
            ->select('games.*',
                'game_release_dates.release_date',
                'game_release_dates.is_released',
                'game_release_dates.upcoming_date',
                'game_release_dates.release_year')
            ->where('game_release_dates.region', $region)
            ->where('games.review_count', '>', '2')
            ->orderBy('games.rating_avg', 'desc');

        $topRatedCounter = $games->get()->count();
        return $topRatedCounter;
    }

    /**
     * @param $region
     * @param $year
     * @param $month
     * @return mixed
     */
    public function getByMonthAllRatings($region, $year, $month)
    {
        $games = DB::table('games')
            ->join('game_release_dates', 'games.id', '=', 'game_release_dates.game_id')
            ->select('games.*',
                'game_release_dates.release_date',
                'game_release_dates.is_released',
                'game_release_dates.upcoming_date',
                'game_release_dates.release_year')
            ->where('game_release_dates.region', $region)
            ->whereYear('game_release_dates.release_date', '=', $year)
            ->whereMonth('game_release_dates.release_date', '=', $month)
            ->orderBy('games.rating_avg', 'desc');
        $games = $games->get();
        return $games;
    }

    /**
     * @param $region
     * @param $year
     * @param $month
     * @return mixed
     */
    public function getByMonthWithRanks($region, $year, $month)
    {
        $games = DB::table('games')
            ->join('game_release_dates', 'games.id', '=', 'game_release_dates.game_id')
            ->select('games.*',
                'game_release_dates.release_date',
                'game_release_dates.is_released',
                'game_release_dates.upcoming_date',
                'game_release_dates.release_year')
            ->where('game_release_dates.region', $region)
            ->whereYear('game_release_dates.release_date', '=', $year)
            ->whereMonth('game_release_dates.release_date', '=', $month)
            ->whereNotNull('games.game_rank')
            ->orderBy('games.rating_avg', 'desc');
        $games = $games->get();
        return $games;
    }

    /**
     * @param $region
     * @param $year
     * @param $month
     * @return mixed
     */
    public function getByMonthLowReviewCount($region, $year, $month)
    {
        $games = DB::table('games')
            ->join('game_release_dates', 'games.id', '=', 'game_release_dates.game_id')
            ->select('games.*',
                'game_release_dates.release_date',
                'game_release_dates.is_released',
                'game_release_dates.upcoming_date',
                'game_release_dates.release_year')
            ->where('game_release_dates.region', $region)
            ->whereYear('game_release_dates.release_date', '=', $year)
            ->whereMonth('game_release_dates.release_date', '=', $month)
            ->whereNotNull('games.rating_avg')
            ->where('review_count', '<', 3)
            ->orderBy('games.rating_avg', 'desc');
        $games = $games->get();
        return $games;
    }

    /**
     * @param $region
     * @param $year
     * @param $month
     * @return mixed
     */
    public function getByMonthNoReviews($region, $year, $month)
    {
        $games = DB::table('games')
            ->join('game_release_dates', 'games.id', '=', 'game_release_dates.game_id')
            ->select('games.*',
                'game_release_dates.release_date',
                'game_release_dates.is_released',
                'game_release_dates.upcoming_date',
                'game_release_dates.release_year')
            ->where('game_release_dates.region', $region)
            ->whereYear('game_release_dates.release_date', '=', $year)
            ->whereMonth('game_release_dates.release_date', '=', $month)
            ->where('review_count', '=', 0)
            ->orderBy('game_release_dates.release_date', 'asc');
        $games = $games->get();
        return $games;
    }

}