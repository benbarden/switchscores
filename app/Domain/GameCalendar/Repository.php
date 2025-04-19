<?php

namespace App\Domain\GameCalendar;

use App\Models\GameCalendarStat;
use App\Models\Game;

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

    public function getList($year, $month)
    {
        return Game::whereYear('games.eu_release_date', '=', $year)
            ->whereMonth('games.eu_release_date', '=', $month)
            ->where(function ($query) {
                $query->where('format_digital', '<>', Game::FORMAT_DELISTED)
                    ->orWhereNull('format_digital');
            })
            ->orderBy('games.eu_release_date', 'asc')
            ->orderBy('games.title', 'asc')->get();
    }

    public function byYear($consoleId, $year)
    {
        return Game::where('console_id', $consoleId)
            ->whereYear('games.eu_release_date', '=', $year)
            ->where(function ($query) {
                $query->where('format_digital', '<>', Game::FORMAT_DELISTED)
                    ->orWhereNull('format_digital');
            })
            ->orderBy('games.eu_release_date', 'asc')
            ->orderBy('games.title', 'asc')->get();
    }
}