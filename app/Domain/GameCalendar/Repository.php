<?php

namespace App\Domain\GameCalendar;

use App\Domain\Cache\CacheManager;
use App\Models\GameCalendarStat;
use App\Models\Game;
use Illuminate\Support\Facades\Cache;

class Repository
{
    public function __construct(
        private CacheManager $cache
    ){

    }
    public function getStat($consoleId, $year, $month)
    {
        $monthName = $year.'-'.$month;

        // cache for 24 hours
        $gameCalendarStat = $this->cache->remember("gamecalendar-c$consoleId-$monthName-stat", 86400, function() use ($consoleId, $monthName) {
            return GameCalendarStat::where('console_id', $consoleId)->where('month_name', $monthName)->first();
        });
        return $gameCalendarStat;
    }

    public function getListByConsole($consoleId, $year, $month)
    {
        return Game::where('console_id', $consoleId)
            ->whereYear('games.eu_release_date', '=', $year)
            ->whereMonth('games.eu_release_date', '=', $month)
            ->where(function ($query) {
                $query->where('format_digital', '<>', Game::FORMAT_DELISTED)
                    ->orWhereNull('format_digital');
            })
            ->orderBy('games.eu_release_date', 'asc')
            ->orderBy('games.title', 'asc')->get();
    }

    /**
     * @deprecated
     * @param $year
     * @param $month
     * @return mixed
     */
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

    public function byYear($consoleId, $year, $includeLowQuality = true)
    {
        $gameList = Game::where('console_id', $consoleId)
            ->whereYear('games.eu_release_date', '=', $year)
            ->where(function ($query) {
                $query->where('format_digital', '<>', Game::FORMAT_DELISTED)
                    ->orWhereNull('format_digital');
            });

        if (!$includeLowQuality) {
            $gameList = $gameList->where('is_low_quality', 0);
        }

        $gameList = $gameList->orderBy('games.eu_release_date', 'asc')->orderBy('games.title', 'asc')->get();
        return $gameList;
    }
}