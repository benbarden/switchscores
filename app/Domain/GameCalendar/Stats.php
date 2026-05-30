<?php

namespace App\Domain\GameCalendar;

use App\Models\Game;

class Stats
{
    public function calendarStatCount($consoleId, $year, $month)
    {
        return Game::where('games.console_id', '=', $consoleId)
            ->whereYear('games.eu_release_date', '=', $year)
            ->whereMonth('games.eu_release_date', '=', $month)
            ->active()
            ->orderBy('games.eu_release_date', 'asc')
            ->orderBy('games.title', 'asc')->count();
    }

    public function lowQualityCount($consoleId, $year, $month)
    {
        return Game::where('games.console_id', '=', $consoleId)
            ->whereYear('games.eu_release_date', '=', $year)
            ->whereMonth('games.eu_release_date', '=', $month)
            ->where('games.is_low_quality', 1)
            ->active()
            ->orderBy('games.eu_release_date', 'asc')
            ->orderBy('games.title', 'asc')->count();
    }

    public function yearCount($consoleId, $year)
    {
        return Game::where('games.console_id', '=', $consoleId)
            ->whereYear('games.eu_release_date', '=', $year)
            ->active()
            ->orderBy('games.eu_release_date', 'asc')
            ->orderBy('games.title', 'asc')->count();
    }
}