<?php

namespace App\Http\Controllers\PublicSite\Games;

use App\Domain\GameLists\DbQueries as GameListsDbQueries;
use App\Domain\GameLists\Repository as GameListsRepository;
use App\Domain\ViewBreadcrumbs\MainSite as Breadcrumbs;
use App\Traits\SwitchServices;
use Illuminate\Routing\Controller as Controller;

class BrowseByDateController extends Controller
{
    use SwitchServices;

    protected $repoGameLists;
    protected $dbGameLists;
    protected $viewBreadcrumbs;

    public function __construct(
        GameListsRepository $repoGameLists,
        GameListsDbQueries $dbGameLists,
        Breadcrumbs $viewBreadcrumbs
    )
    {
        $this->repoGameLists = $repoGameLists;
        $this->dbGameLists = $dbGameLists;
        $this->viewBreadcrumbs = $viewBreadcrumbs;
    }

    public function landing()
    {
        $bindings = [];

        $bindings['TopTitle'] = 'Nintendo Switch games by release date';
        $bindings['PageTitle'] = 'Nintendo Switch games by release date';
        $bindings['crumbNav'] = $this->viewBreadcrumbs->gamesSubpage('By date');

        $dateList = $this->getServiceGameCalendar()->getAllowedDates(false);
        $dateListArray = [];

        $allowedYears = $this->getServiceGameCalendar()->getAllowedYears();

        foreach ($allowedYears as $allowedYear) {
            $dateListArray[$allowedYear] = [];
        }

        if ($dateList) {

            foreach ($dateList as $date) {

                list($dateYear, $dateMonth) = explode('-', $date);

                $gameCalendarStat = $this->getServiceGameCalendar()->getStat($dateYear, $dateMonth);
                if ($gameCalendarStat) {
                    $dateCount = $gameCalendarStat->released_count;
                } else {
                    $dateCount = 0;
                }

                if ($dateCount == 0) {
                    continue;
                }

                $dateListArray[] = [
                    'DateRaw' => $date,
                    'GameCount' => $dateCount,
                ];

                if (!in_array($dateYear, $allowedYears)) {
                    throw new \Exception('Year '.$dateYear.' could not be found in allowedYears');
                }

                $dateListArray[$dateYear][] = [
                    'DateRaw' => $date,
                    'GameCount' => $dateCount,
                ];

            }

        }

        $allowedYearsReversed = array_reverse($allowedYears);
        $bindings['AllowedYears'] = $allowedYearsReversed;

        $bindings['DateList'] = $dateListArray;
        foreach ($allowedYearsReversed as $allowedYear) {
            $bindings['DateList'.$allowedYear] = $dateListArray[$allowedYear];
        }

        return view('public.games.browse.byDateLanding', $bindings);
    }

    public function page($dateUrl)
    {
        $dates = $this->getServiceGameCalendar()->getAllowedDates();
        if (!in_array($dateUrl, $dates)) {
            abort(404);
        }

        $bindings = [];

        $dtDate = new \DateTime($dateUrl);
        $dtDateDesc = $dtDate->format('M Y');

        $calendarYear = $dtDate->format('Y');
        $calendarMonth = $dtDate->format('m');
        $gamesByMonthList = $this->getServiceGameCalendar()->getList($calendarYear, $calendarMonth);
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

        $bindings['TopTitle'] = 'Nintendo Switch games by release date: '.$dtDateDesc;
        $bindings['PageTitle'] = 'Nintendo Switch games by release date: '.$dtDateDesc;
        $bindings['crumbNav'] = $this->viewBreadcrumbs->gamesByDateSubpage($dtDateDesc);

        $bindings['CalendarDateDesc'] = $dtDateDesc;
        $bindings['CalendarDateUrl'] = $dateUrl;

        // Top Rated by month
        $yearMonth = $calendarYear.$calendarMonth;
        $bindings['GamesRatingsWithRanks'] = $this->getServiceGameRankYearMonth()->getList($yearMonth, 50);

        return view('public.games.browse.byDatePage', $bindings);
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