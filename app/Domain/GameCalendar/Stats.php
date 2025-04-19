<?php

namespace App\Domain\GameCalendar;

use App\Models\Game;

class Stats
{
    public function calendarStatCount($year, $month)
    {
        return Game::whereYear('games.eu_release_date', '=', $year)
            ->whereMonth('games.eu_release_date', '=', $month)
            ->where(function ($query) {
                $query->where('format_digital', '<>', Game::FORMAT_DELISTED)
                    ->orWhereNull('format_digital');
            })
            ->orderBy('games.eu_release_date', 'asc')
            ->orderBy('games.title', 'asc')->count();
    }
}