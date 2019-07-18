<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as Controller;

use App\Services\ServiceContainer;

class GamesBrowseController extends Controller
{
    public function byTitleLanding()
    {
        $bindings = [];

        $bindings['TopTitle'] = 'Browse Switch games by title';
        $bindings['PageTitle'] = 'Browse Switch games by title';

        $bindings['LetterList'] = range('A', 'Z');

        return view('games.browse.byTitleLanding', $bindings);
    }

    public function byTitlePage($letter)
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $regionCode = \Request::get('regionCode');

        $serviceGameReleaseDate = $serviceContainer->getGameReleaseDateService();

        $bindings = [];

        $gamesList = $serviceGameReleaseDate->getReleasedByLetter($regionCode, $letter);

        $bindings['GamesList'] = $gamesList;
        $bindings['GameLetter'] = $letter;

        $bindings['TopTitle'] = 'Browse Switch games by title: '.$letter;
        $bindings['PageTitle'] = 'Browse Switch games by title: '.$letter;

        return view('games.browse.byTitlePage', $bindings);
    }

    public function byPrimaryTypeLanding()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $servicePrimaryType = $serviceContainer->getGamePrimaryTypeService();

        $bindings = [];

        $bindings['PrimaryTypeList'] = $servicePrimaryType->getAll();

        $bindings['PageTitle'] = 'Browse Switch games by primary type';
        $bindings['TopTitle'] = 'Browse Switch games by primary type';

        return view('games.browse.byPrimaryTypeLanding', $bindings);
    }

    public function byPrimaryTypePage($primaryType)
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $regionCode = \Request::get('regionCode');

        $servicePrimaryType = $serviceContainer->getGamePrimaryTypeService();
        $serviceGameReleaseDate = $serviceContainer->getGameReleaseDateService();

        $bindings = [];

        $primaryType = $servicePrimaryType->getByLinkTitle($primaryType);
        if (!$primaryType) abort(404);

        $primaryTypeId = $primaryType->id;
        $primaryTypeName = $primaryType->primary_type;

        $gameList = $serviceGameReleaseDate->getReleasedByPrimaryType($primaryTypeId, $regionCode);

        $bindings['PrimaryType'] = $primaryType;
        $bindings['GameList'] = $gameList;

        $bindings['PageTitle'] = 'Browse Switch games by primary type: '.$primaryTypeName;
        $bindings['TopTitle'] = 'Browse Switch games by primary type: '.$primaryTypeName;

        return view('games.browse.byPrimaryTypePage', $bindings);
    }

    public function bySeriesLanding()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $serviceGameSeries = $serviceContainer->getGameSeriesService();

        $bindings = [];

        $bindings['SeriesList'] = $serviceGameSeries->getAll();

        $bindings['PageTitle'] = 'Browse Switch games by series';
        $bindings['TopTitle'] = 'Browse Switch games by series';

        return view('games.browse.bySeriesLanding', $bindings);
    }

    public function bySeriesPage($series)
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $regionCode = \Request::get('regionCode');

        $serviceGameSeries = $serviceContainer->getGameSeriesService();
        $serviceGameReleaseDate = $serviceContainer->getGameReleaseDateService();

        $bindings = [];

        $gameSeries = $serviceGameSeries->getByLinkTitle($series);
        if (!$gameSeries) abort(404);

        $seriesId = $gameSeries->id;
        $seriesName = $gameSeries->series;

        $gameList = $serviceGameReleaseDate->getReleasedBySeries($seriesId, $regionCode);

        $bindings['GameList'] = $gameList;

        $bindings['PageTitle'] = 'Browse Switch games by series: '.$seriesName;
        $bindings['TopTitle'] = 'Browse Switch games by series: '.$seriesName;

        return view('games.browse.bySeriesPage', $bindings);
    }

    public function byTagLanding()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $serviceTag = $serviceContainer->getTagService();

        $bindings = [];

        $bindings['TagList'] = $serviceTag->getAll();

        $bindings['PageTitle'] = 'Browse Switch games by tag';
        $bindings['TopTitle'] = 'Browse Switch games by tag';

        return view('games.browse.byTagLanding', $bindings);
    }

    public function byTagPage($tag)
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $regionCode = \Request::get('regionCode');

        $serviceTag = $serviceContainer->getTagService();
        $serviceGameFilter = $serviceContainer->getGameFilterListService();

        $bindings = [];

        $tag = $serviceTag->getByLinkTitle($tag);

        if (!$tag) abort(404);

        $tagId = $tag->id;
        $tagName = $tag->tag_name;

        $gameList = $serviceGameFilter->getByTagWithDates($regionCode, $tagId);

        $bindings['GameList'] = $gameList;

        $bindings['PageTitle'] = 'Browse Switch games by tag: '.$tagName;
        $bindings['TopTitle'] = 'Browse Switch games by tag: '.$tagName;

        return view('games.browse.byTagPage', $bindings);
    }

    public function byDateLanding()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $regionCode = \Request::get('regionCode');
        $regionCodeDesc = null;
        switch ($regionCode) {
            case 'eu':
                $regionCodeDesc = 'Europe';
                break;
            case 'us':
                $regionCodeDesc = 'US';
                break;
            case 'jp':
                $regionCodeDesc = 'Japan';
                break;
            default:
                abort(404);
                break;
        }

        $serviceGameCalendar = $serviceContainer->getGameCalendarService();

        $bindings = [];

        if ($regionCodeDesc) {
            $bindings['RegionCodeDesc'] = $regionCodeDesc;
        }

        $bindings['TopTitle'] = 'Nintendo Switch - Release calendar';
        $bindings['PageTitle'] = 'Nintendo Switch - Release calendar';

        $dateList = $serviceGameCalendar->getAllowedDates(false);
        $dateListArray = [];

        $dateListArray2017 = [];
        $dateListArray2018 = [];
        $dateListArray2019 = [];

        if ($dateList) {

            foreach ($dateList as $date) {

                list($dateYear, $dateMonth) = explode('-', $date);

                $gameCalendarStat = $serviceGameCalendar->getStat($regionCode, $dateYear, $dateMonth);
                if ($gameCalendarStat) {
                    $dateCount = $gameCalendarStat->released_count;
                } else {
                    $dateCount = 0;
                }

                $dateListArray[] = [
                    'DateRaw' => $date,
                    'GameCount' => $dateCount,
                ];

                switch ($dateYear) {
                    case 2017:
                        $dateListArray2017[] = [
                            'DateRaw' => $date,
                            'GameCount' => $dateCount,
                        ];
                        break;
                    case 2018:
                        $dateListArray2018[] = [
                            'DateRaw' => $date,
                            'GameCount' => $dateCount,
                        ];
                        break;
                    case 2019:
                        $dateListArray2019[] = [
                            'DateRaw' => $date,
                            'GameCount' => $dateCount,
                        ];
                        break;
                }

            }

        }

        $bindings['DateList'] = $dateListArray;
        $bindings['DateList2017'] = $dateListArray2017;
        $bindings['DateList2018'] = $dateListArray2018;
        $bindings['DateList2019'] = $dateListArray2019;

        return view('games.browse.byDateLanding', $bindings);
    }

    public function byDatePage($dateUrl)
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $regionCode = \Request::get('regionCode');

        $serviceGameCalendar = $serviceContainer->getGameCalendarService();

        $dates = $serviceGameCalendar->getAllowedDates();
        if (!in_array($dateUrl, $dates)) {
            abort(404);
        }

        $bindings = [];

        $dtDate = new \DateTime($dateUrl);
        $dtDateDesc = $dtDate->format('M Y');

        $calendarYear = $dtDate->format('Y');
        $calendarMonth = $dtDate->format('m');
        $gamesByMonthList = $serviceGameCalendar->getList($regionCode, $calendarYear, $calendarMonth);
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
            $gameDate = $game->release_date;
            $calendarGames[$gameDate]['games'][] = $game;
        }
        $bindings['CalendarGamesList'] = $calendarGames;

        $bindings['TopTitle'] = 'Nintendo Switch - Release calendar: '.$dtDateDesc;
        $bindings['PageTitle'] = 'Nintendo Switch - Release calendar: '.$dtDateDesc;

        $bindings['CalendarDateDesc'] = $dtDateDesc;
        $bindings['CalendarDateUrl'] = $dateUrl;

        return view('games.browse.byDatePage', $bindings);
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
    public function createDateRange($startDate, $endDate, $format = "Y-m-d")
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
