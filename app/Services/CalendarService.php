<?php


namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class CalendarService
{
    /**
     * @param $region
     * @param $year
     * @param $month
     * @return mixed
     */
    public function getList($region, $year, $month)
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
            ->orderBy('game_release_dates.release_date', 'asc')
            ->orderBy('games.title', 'asc');

        $games = $games->get();
        return $games;
    }

    /**
     * @param $region
     * @param $year
     * @param $month
     * @return mixed
     */
    public function getRatings($region, $year, $month)
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

}