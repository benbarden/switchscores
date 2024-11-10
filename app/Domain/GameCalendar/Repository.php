<?php

namespace App\Domain\GameCalendar;

use App\Models\GameCalendarStat;

class Repository
{
    public function getStat($year, $month)
    {
        $monthName = $year.'-'.$month;
        $gameCalendarStat = GameCalendarStat::where('month_name', $monthName)->get();

        if ($gameCalendarStat) {
            return $gameCalendarStat->first();
        } else {
            return null;
        }
    }
}