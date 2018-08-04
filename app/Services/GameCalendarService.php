<?php


namespace App\Services;

use Illuminate\Support\Facades\DB;

use App\GameCalendarStat;


class GameCalendarService
{
    /**
     * @param $region
     * @param $year
     * @param $month
     * @return GameCalendarStat
     */
    public function getStat($region, $year, $month)
    {
        $monthName = $year.'-'.$month;
        $gameCalendarStat = GameCalendarStat::
            where('region', $region)
            ->where('month_name', $monthName)
            ->get();

        if ($gameCalendarStat) {
            return $gameCalendarStat->first();
        } else {
            return null;
        }
    }

    /**
     * @param bool $reverse
     * @return array
     */
    public function getAllowedDates($reverse = true)
    {
        $dates = [];

        for ($i=2017; $i<date('Y')+1; $i++) {

            for ($j=1; $j<13; $j++) {

                // Start from March 2017
                if ($i == 2017 && $j < 3) continue;
                // Don't go beyond the current month and year
                if ($i == date('Y') && $j > date('m')) break;
                // Good to go
                $dateToAdd = $i.'-'.str_pad($j, 2, '0', STR_PAD_LEFT);
                $dates[] = $dateToAdd;

            }

        }

        if ($reverse) {
            $dates = array_reverse($dates);
        }

        return $dates;
    }

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
    public function getListCount($region, $year, $month)
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

        $games = $games->count();
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