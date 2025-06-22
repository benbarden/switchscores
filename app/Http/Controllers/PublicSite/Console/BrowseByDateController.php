<?php

namespace App\Http\Controllers\PublicSite\Console;

use App\Domain\ReviewLink\Stats as ReviewLinkStatsRepository;
use App\Domain\TopRated\DbQueries as TopRatedDbQueries;
use App\Domain\GameCalendar\Repository as GameCalendarRepository;
use App\Domain\GameCalendar\AllowedDates as GameCalendarAllowedDates;

use App\Models\Console;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Support\Collection;


class BrowseByDateController extends Controller
{
    public function __construct(
        private TopRatedDbQueries $dbTopRated,
        private GameCalendarRepository $repoGameCalendar,
        private GameCalendarAllowedDates $allowedDates,
        private ReviewLinkStatsRepository $repoReviewLinkStats,
    )
    {
    }

    public function byYear(Console $console, $year)
    {
        $consoleName = $console->name;
        $consoleId = $console->id;

        $pageTitle = 'Nintendo '.$consoleName.' games released in '.$year;
        $breadcrumbs = resolve('View/Breadcrumbs/MainSite')->consoleSubpage($pageTitle, $console);
        $bindings = resolve('View/Bindings/MainSite')->setBreadcrumbs($breadcrumbs)->generateMain($pageTitle);

        $bindings['Console'] = $console;

        // Month by month links
        $dateList = $this->allowedDates->allowedDatesByConsoleAndYear($consoleId, $year);
        $dateListArray = [];

        $allowedYears = [$year];

        foreach ($allowedYears as $allowedYear) {
            $dateListArray[$allowedYear] = [];
        }

        if ($dateList) {

            foreach ($dateList as $date) {

                list($dateYear, $dateMonth) = explode('-', $date);

                $gameCalendarStat = $this->repoGameCalendar->getStat($consoleId, $dateYear, $dateMonth);
                if (!$gameCalendarStat) continue;

                $dateCount = $gameCalendarStat->released_count;
                if ($dateCount == 0) continue;

                $dateListArray[] = ['DateRaw' => $date, 'GameCount' => $dateCount,];

                if (!in_array($dateYear, $allowedYears)) {
                    throw new \Exception('Year '.$dateYear.' could not be found in allowedYears');
                }

                $dateListArray[$dateYear][] = [
                    'DateRaw' => $date,
                    'GameCount' => $dateCount,
                ];

            }

        }

        // Review stats
        // Review counts
        $reviewDateList = $this->allowedDates->allowedDatesByConsole($consoleId, false);
        $reviewDateListArray = [];
        $reviewTotal = 0;

        if ($dateList) {

            foreach ($reviewDateList as $date) {

                list($dateYear, $dateMonth) = explode('-', $date);

                if ($dateYear != $year) continue;

                $reviewLinkStat = $this->repoReviewLinkStats->totalActiveByConsoleYearMonth($consoleId, $dateYear, $dateMonth);
                if ($reviewLinkStat) {
                    $dateCount = $reviewLinkStat;
                } else {
                    $dateCount = 0;
                }

                if ($dateCount == 0) continue;

                $reviewDateListArray[] = [
                    'DateRaw' => $date,
                    'ReviewCount' => $dateCount,
                ];
                $reviewTotal += $dateCount;

            }

        }

        // Score distribution
        $bindings['ScoreDistributionByYear'] = $this->repoReviewLinkStats->scoreDistributionByConsoleAndYear($consoleId, $year);

        // Ranked/Unranked count
        $bindings['RankedCountByYear'] = $this->dbTopRated->rankedCountByConsoleAndYear($consoleId, $year);

        // Review count stats
        $bindings['ReviewCountStatsByYear'] = $this->repoReviewLinkStats->reviewCountStatsByConsoleAndYear($consoleId, $year);

        $bindings['ReviewDateList'] = $reviewDateListArray;
        $bindings['ReviewTotal'.$year] = $reviewTotal;

        // General bindings
        $bindings['Year'] = $year;
        $bindings['ConsoleName'] = $consoleName;
        $bindings['DateList'] = $dateListArray;
        $bindings['DateList'.$year] = $dateListArray[$year];

        return view('public.console.by-year.landing', $bindings);
    }

    public function byMonth(Console $console, $year, $month)
    {
        $consoleName = $console->name;
        $consoleId = $console->id;

        $dates = $this->allowedDates->allowedDatesByConsoleAndYear($consoleId, $year);
        $dateUrl = $year.'-'.$month;
        if (!in_array($dateUrl, $dates)) {
            abort(404);
        }

        $dtDate = new \DateTime($dateUrl);
        $dtDateDesc = $dtDate->format('M Y');

        $pageTitle = 'Nintendo '.$consoleName.' games released in '.$dtDateDesc;
        $breadcrumbs = resolve('View/Breadcrumbs/MainSite')->consoleSubpage($pageTitle, $console);
        $bindings = resolve('View/Bindings/MainSite')->setBreadcrumbs($breadcrumbs)->generateMain($pageTitle);

        $bindings['Console'] = $console;

        // General bindings
        $bindings['Year'] = $year;
        $bindings['ConsoleName'] = $consoleName;

        $calendarYear = $dtDate->format('Y');
        $calendarMonth = $dtDate->format('m');
        $gamesByMonthList = $this->repoGameCalendar->getListByConsole($consoleId, $calendarYear, $calendarMonth);
        $bindings['GamesByMonthList'] = $gamesByMonthList;

        // Get all dates
        $daysInMonth = $dtDate->format('t');
        $dateFrom = sprintf('%s-%s-01', $calendarYear, $calendarMonth);
        $dateTo = sprintf('%s-%s-%s', $calendarYear, $calendarMonth, $daysInMonth);
        $dateList = $this->createDateRange($dateFrom, $dateTo);

        // Get games by date
        $calendarGames = [];
        foreach ($dateList as $dateListItem) {
            $calendarGames[$dateListItem] = [
                'games' => []
            ];
        }
        foreach ($gamesByMonthList as $game) {
            $gameDate = $game->eu_release_date;
            $calendarGames[$gameDate]['games'][] = $game;
        }
        $bindings['CalendarGamesList'] = $calendarGames;

        $bindings['CalendarDateDesc'] = $dtDateDesc;
        $bindings['CalendarDateUrl'] = $dateUrl;

        // Top Rated by month
        $yearMonth = $calendarYear.$calendarMonth;
        $bindings['GamesRatingsWithRanks'] = $this->dbTopRated->byYearMonth($consoleId, $yearMonth, 50);

        return view('public.console.by-month.landing', $bindings);
    }

    /**
     * Returns every date between two dates as an array
     * Source: https://www.codementor.io/tips/1170438972/how-to-get-an-array-of-all-dates-between-two-dates-in-php
     * @param string $startDate the start of the date range
     * @param string $endDate the end of the date range
     * @param string $format DateTime format, default is Y-m-d
     * @return array returns every date between $startDate and $endDate, formatted as "Y-m-d"
     * @throws \Exception
     */
    private function createDateRange($startDate, $endDate, $format = "Y-m-d")
    {
        $begin = new \DateTime($startDate);
        $end = new \DateTime($endDate);
        $end->add(new \DateInterval('P1D')); // add 1 day so it hits the end of the month

        $interval = new \DateInterval('P1D'); // 1 day
        $dateRange = new \DatePeriod($begin, $interval, $end);

        $range = [];
        foreach ($dateRange as $date) {
            $range[] = $date->format($format);
        }

        return $range;
    }
}