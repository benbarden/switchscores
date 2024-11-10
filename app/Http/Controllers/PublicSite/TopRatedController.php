<?php

namespace App\Http\Controllers\PublicSite;

use Illuminate\Routing\Controller as Controller;

use App\Domain\TopRated\DbQueries as TopRatedDbQueries;
use App\Domain\ViewBreadcrumbs\MainSite as Breadcrumbs;

use App\Traits\SwitchServices;

class TopRatedController extends Controller
{
    use SwitchServices;

    public function __construct(
        private TopRatedDbQueries $dbTopRated,
        private Breadcrumbs $viewBreadcrumbs
    )
    {
    }

    public function landing()
    {
        $bindings = [];

        $thisYear = date('Y');
        $lastYear = $thisYear - 1;
        $bindings['Year'] = $thisYear;
        $bindings['LastYear'] = $lastYear;
        $bindings['TopRatedThisYear'] = $this->dbTopRated->byYear($thisYear, 15);
        $bindings['TopRatedLastYear'] = $this->dbTopRated->byYear($lastYear, 15);
        $bindings['TopRatedAllTime'] = $this->dbTopRated->getList(1, 15);

        $bindings['TopTitle'] = 'Top Rated Nintendo Switch games';
        $bindings['PageTitle'] = 'Top Rated Nintendo Switch games';
        $bindings['crumbNav'] = $this->viewBreadcrumbs->topLevelPage('Top Rated');

        return view('public.topRated.landing', $bindings);
    }

    public function allTime()
    {
        $bindings = [];

        $gamesList = $this->dbTopRated->getList(1, 100);

        $bindings['TopRatedAllTime'] = $gamesList;

        $bindings['TopTitle'] = 'Top 100 Nintendo Switch games';
        $bindings['PageTitle'] = 'Top 100 Nintendo Switch games';
        $bindings['crumbNav'] = $this->viewBreadcrumbs->topRatedSubpage('All-time');

        return view('public.topRated.allTime', $bindings);
    }

    public function allTimePage($page)
    {
        $page = (int) $page;
        if (!$page) abort(404);
        if ($page > 5) abort(404);
        //if ($page == 'favicon.ico') abort(404);

        if ($page) {
            $maxRank = $page * 100;
            $minRank = $maxRank - 99;
        } else {
            $minRank = 1;
            $maxRank = 100;
        }

        $gamesList = $this->dbTopRated->getList($minRank, $maxRank);

        $bindings = [];
        $bindings['TopRatedAllTime'] = $gamesList;

        $bindings['TopTitle'] = 'Top 100 Nintendo Switch games';
        $bindings['PageTitle'] = 'Top 100 Nintendo Switch games';
        $bindings['crumbNav'] = $this->viewBreadcrumbs->topRatedSubpage('All-time');

        return view('public.topRated.allTime', $bindings);
    }

    public function byYear($year)
    {
        $allowedYears = $this->getServiceGameCalendar()->getAllowedYears();
        if (!in_array($year, $allowedYears)) {
            abort(404);
        }

        $bindings = [];

        $gamesList = $this->dbTopRated->byYear($year, 100);

        $bindings['TopRatedByYear'] = $gamesList;
        $bindings['GamesTableSort'] = "[5, 'desc']";
        $bindings['Year'] = $year;

        $bindings['TopTitle'] = 'Top 100 Nintendo Switch games - released in '.$year;
        $bindings['PageTitle'] = 'Top 100 Nintendo Switch games - released in '.$year;
        $bindings['crumbNav'] = $this->viewBreadcrumbs->topRatedSubpage($year);

        return view('public.topRated.byYear', $bindings);
    }

}
