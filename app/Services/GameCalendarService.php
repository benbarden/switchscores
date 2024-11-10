<?php


namespace App\Services;

use App\Models\Game;
use App\Models\GameCalendarStat;
use Illuminate\Support\Facades\DB;

class GameCalendarService
{
    /**
     * @deprecated
     * @return int[]
     */
    public function getAllowedYears()
    {
        $releaseYears = [];
        for ($year = 2017; $year <= date('Y'); $year++) {
            $releaseYears[] = $year;
        }
        return $releaseYears;
    }

    /**
     * @param bool $reverse
     * @return array
     */
    public function getAllowedDates($reverse = true)
    {
        $allowedYears = $this->getAllowedYears();

        $dates = [];

        foreach ($allowedYears as $allowedYear) {

            for ($j=1; $j<13; $j++) {

                // Start from March 2017
                if ($allowedYear == 2017 && $j < 3) continue;
                // Don't go beyond the current month and year
                if ($allowedYear == date('Y') && $j > date('m')+1) break;
                // Good to go
                $dateToAdd = $allowedYear.'-'.str_pad($j, 2, '0', STR_PAD_LEFT);
                $dates[] = $dateToAdd;

            }

        }

        if ($reverse) {
            $dates = array_reverse($dates);
        }

        return $dates;
    }

    /**
     * @param $year
     * @param $month
     * @return mixed
     */
    public function getList($year, $month)
    {
        $games = DB::table('games')
            ->select('games.*')
            ->whereYear('games.eu_release_date', '=', $year)
            ->whereMonth('games.eu_release_date', '=', $month)
            ->where('format_digital', '<>', Game::FORMAT_DELISTED)
            ->orderBy('games.eu_release_date', 'asc')
            ->orderBy('games.title', 'asc');

        $games = $games->get();
        return $games;
    }

    /**
     * @param $year
     * @param $month
     * @return mixed
     */
    public function getListCount($year, $month)
    {
        $games = DB::table('games')
            ->select('games.*')
            ->where('games.eu_is_released', 1)
            ->whereYear('games.eu_release_date', '=', $year)
            ->whereMonth('games.eu_release_date', '=', $month)
            ->where('format_digital', '<>', Game::FORMAT_DELISTED)
            ->orderBy('games.eu_release_date', 'asc')
            ->orderBy('games.title', 'asc');

        $games = $games->count();
        return $games;
    }
}