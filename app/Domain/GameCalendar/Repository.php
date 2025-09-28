<?php

namespace App\Domain\GameCalendar;

use App\Domain\Repository\AbstractRepository;
use App\Enums\CacheDuration;
use App\Models\GameCalendarStat;
use App\Models\Game;
use Illuminate\Support\Facades\DB;

class Repository extends AbstractRepository
{
    protected function getCachePrefix(): string
    {
        return "gamecalendar";
    }

    public function getStat($consoleId, $year, $month)
    {
        $monthName = $year.'-'.$month;

        $cacheKey = $this->buildCacheKey("c$consoleId-$monthName-stat");
        return $this->rememberCache($cacheKey, CacheDuration::ONE_DAY, function() use ($consoleId, $monthName) {
            return GameCalendarStat::where('console_id', $consoleId)->where('month_name', $monthName)->first();
        });
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

    public function getListByConsoleAndQualityFilter($consoleId, $year, $month, $isLowQuality)
    {
        return Game::where('console_id', $consoleId)
            ->whereYear('games.eu_release_date', '=', $year)
            ->whereMonth('games.eu_release_date', '=', $month)
            ->where('games.is_low_quality', $isLowQuality)
            ->where(function ($query) {
                $query->where('format_digital', '<>', Game::FORMAT_DELISTED)
                    ->orWhereNull('format_digital');
            })
            ->orderBy('games.eu_release_date', 'asc')
            ->orderBy('games.title', 'asc')->get();
    }

    public function getMostCommonCategoryByMonth($consoleId, $year, $month)
    {
        return DB::select("
            SELECT c.name, count(*) as count
            FROM games g
            JOIN categories c ON c.id = g.category_id
            WHERE g.console_id = ? AND YEAR(g.eu_release_date) = ? AND MONTH(g.eu_release_date) = ?
            AND (format_digital <> ? OR format_digital IS NULL)
            GROUP BY g.category_id
            ORDER BY count DESC, RAND() LIMIT 1
        ", [$consoleId, $year, $month, Game::FORMAT_DELISTED]);
    }

    public function getTopPublishersByMonth($consoleId, $year, $month)
    {
        return DB::select("
            SELECT gc.*, count(*) as count
            FROM games g
            join game_publishers gp on g.id = gp.game_id
            join games_companies gc on gc.id = gp.publisher_id
            where g.console_id = ? and g.is_low_quality = 0
            and year(g.eu_release_date) = ? and month(g.eu_release_date) = ?
            AND (format_digital <> ? OR format_digital IS NULL)
            group by gc.id
            order by count(*) desc, gc.name asc
        ", [$consoleId, $year, $month, Game::FORMAT_DELISTED]);
    }

    public function getMonthlyHiddenGems($consoleId, $year, $month)
    {
        return Game::where('console_id', $consoleId)
            ->whereYear('games.eu_release_date', '=', $year)
            ->whereMonth('games.eu_release_date', '=', $month)
            ->where('games.is_low_quality', 0)
            ->where('review_count', '<', 3)
            ->where('review_count', '>', 0)
            ->where(function ($query) {
                $query->where('format_digital', '<>', Game::FORMAT_DELISTED)
                    ->orWhereNull('format_digital');
            })
            ->orderBy('games.rating_avg', 'desc')
            ->orderBy('games.review_count', 'desc')
            ->get();
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