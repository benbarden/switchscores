<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as Controller;

use App\Services\ServiceContainer;

class TopRatedController extends Controller
{
    public function landing()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $regionCode = \Request::get('regionCode');

        $serviceTopRated = $serviceContainer->getTopRatedService();

        $bindings = [];

        $thisYear = date('Y');
        $bindings['Year'] = $thisYear;
        $bindings['TopRatedThisYear'] = $serviceTopRated->getByYear($regionCode, $thisYear, 15);
        $bindings['TopRatedNewReleases'] = $serviceTopRated->getLastXDays($regionCode, 30, 15);
        $bindings['TopRatedAllTime'] = $serviceTopRated->getList($regionCode, 15);

        $bindings['TopTitle'] = 'Top Rated Nintendo Switch games';
        $bindings['PageTitle'] = 'Top Rated Nintendo Switch games';

        return view('topRated.landing', $bindings);
    }

    public function allTime()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $regionCode = \Request::get('regionCode');

        $serviceTopRated = $serviceContainer->getTopRatedService();

        $bindings = [];

        $gamesList = $serviceTopRated->getList($regionCode);

        $bindings['GamesList'] = $gamesList;
        $bindings['GamesTableSort'] = "[5, 'desc']";

        $bindings['TopTitle'] = 'Nintendo Switch Top Rated games';
        $bindings['PageTitle'] = 'Top Rated Nintendo Switch games';

        return view('topRated.allTime', $bindings);
    }

    public function byYear($year)
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $regionCode = \Request::get('regionCode');

        $serviceTopRated = $serviceContainer->getTopRatedService();

        $allowedYears = [2017, 2018, 2019];
        if (!in_array($year, $allowedYears)) {
            abort(404);
        }

        $bindings = [];

        $gamesList = $serviceTopRated->getByYear($regionCode, $year);

        $bindings['GamesList'] = $gamesList;
        $bindings['GamesTableSort'] = "[5, 'desc']";
        $bindings['Year'] = $year;

        $bindings['TopTitle'] = 'Nintendo Switch Top Rated games - '.$year;
        $bindings['PageTitle'] = 'Top Rated Nintendo Switch games - '.$year;

        return view('topRated.byYear', $bindings);
    }

    public function byMonthLanding()
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
                break;
        }

        $serviceGameCalendar = $serviceContainer->getGameCalendarService();

        $bindings = [];

        if ($regionCodeDesc) {
            $bindings['RegionCodeDesc'] = $regionCodeDesc;
        }

        $bindings['TopTitle'] = 'Top Rated Nintendo Switch games - By month';
        $bindings['PageTitle'] = 'Top Rated Nintendo Switch games - By month';

        $dateList = $serviceGameCalendar->getAllowedDates();
        $dateListArray = [];

        if ($dateList) {

            foreach ($dateList as $date) {

                list($dateYear, $dateMonth) = explode('-', $date);

                $gameCalendarStat = $serviceGameCalendar->getStat($regionCode, $dateYear, $dateMonth);
                $dateCount = $gameCalendarStat->released_count;

                $dateListArray[] = [
                    'DateRaw' => $date,
                    'GameCount' => $dateCount,
                ];

            }

        }

        $bindings['DateList'] = $dateListArray;

        return view('topRated.byMonthLanding', $bindings);
    }

    public function byMonthPage($date)
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $regionCode = \Request::get('regionCode');

        $serviceGameCalendar = $serviceContainer->getGameCalendarService();
        $serviceTopRated = $serviceContainer->getTopRatedService();

        $dates = $serviceGameCalendar->getAllowedDates();
        if (!in_array($date, $dates)) {
            abort(404);
        }

        $bindings = [];

        $dtDate = new \DateTime($date);
        $dtDateDesc = $dtDate->format('M Y');

        $calendarYear = $dtDate->format('Y');
        $calendarMonth = $dtDate->format('m');
        //$bindings['GamesByMonthRatings'] = $serviceTopRated->getByMonthAllRatings($regionCode, $calendarYear, $calendarMonth);
        $bindings['GamesRatingsWithRanks'] = $serviceTopRated->getByMonthWithRanks($regionCode, $calendarYear, $calendarMonth);
        $bindings['GamesRatingsLowReviewCount'] = $serviceTopRated->getByMonthLowReviewCount($regionCode, $calendarYear, $calendarMonth);
        $bindings['GamesRatingsNoReviews'] = $serviceTopRated->getByMonthNoReviews($regionCode, $calendarYear, $calendarMonth);

        $bindings['TopTitle'] = 'Top Rated Nintendo Switch games - By month: '.$dtDateDesc;
        $bindings['PageTitle'] = 'Top Rated Nintendo Switch games - By month: '.$dtDateDesc;

        $bindings['CalendarDateDesc'] = $dtDateDesc;

        return view('topRated.byMonthPage', $bindings);
    }

}
