<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as Controller;

use App\Domain\ViewBreadcrumbs\MainSite as Breadcrumbs;

use App\Traits\SwitchServices;

class TopRatedController extends Controller
{
    use SwitchServices;

    protected $viewBreadcrumbs;

    public function __construct(
        Breadcrumbs $viewBreadcrumbs
    )
    {
        $this->viewBreadcrumbs = $viewBreadcrumbs;
    }

    public function landing()
    {
        $serviceGameRankAllTime = $this->getServiceGameRankAllTime();
        $serviceGameRankYear = $this->getServiceGameRankYear();

        $bindings = [];

        $thisYear = date('Y');
        $lastYear = $thisYear - 1;
        $bindings['Year'] = $thisYear;
        $bindings['LastYear'] = $lastYear;
        $bindings['TopRatedThisYear'] = $serviceGameRankYear->getList($thisYear, 15);
        $bindings['TopRatedLastYear'] = $serviceGameRankYear->getList($lastYear, 15);
        $bindings['TopRatedAllTime'] = $serviceGameRankAllTime->getList(1, 15);

        $bindings['TopTitle'] = 'Top Rated Nintendo Switch games';
        $bindings['PageTitle'] = 'Top Rated Nintendo Switch games';
        $bindings['crumbNav'] = $this->viewBreadcrumbs->topLevelPage('Top Rated');

        return view('topRated.landing', $bindings);
    }

    public function multiplayer()
    {
        $serviceGameRankAllTime = $this->getServiceGameRankAllTime();

        $bindings = [];

        $bindings['TopRatedMultiplayer'] = $serviceGameRankAllTime->getList(1, 100, 'multiplayer');

        $bindings['TopTitle'] = 'Top Rated Nintendo Switch multiplayer games';
        $bindings['PageTitle'] = 'Top Rated Nintendo Switch multiplayer games';
        $bindings['crumbNav'] = $this->viewBreadcrumbs->topRatedSubpage('Multiplayer');

        return view('topRated.multiplayer', $bindings);
    }

    public function allTime()
    {
        $bindings = [];

        $serviceGameRankAllTime = $this->getServiceGameRankAllTime();
        $gamesList = $serviceGameRankAllTime->getList(1, 100);

        $bindings['TopRatedAllTime'] = $gamesList;

        $bindings['TopTitle'] = 'Top 100 Nintendo Switch games';
        $bindings['PageTitle'] = 'Top 100 Nintendo Switch games';
        $bindings['crumbNav'] = $this->viewBreadcrumbs->topRatedSubpage('All-time');

        return view('topRated.allTime', $bindings);
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

        $serviceGameRankAllTime = $this->getServiceGameRankAllTime();
        $gamesList = $serviceGameRankAllTime->getList($minRank, $maxRank);

        $bindings = [];
        $bindings['TopRatedAllTime'] = $gamesList;

        $bindings['TopTitle'] = 'Top 100 Nintendo Switch games';
        $bindings['PageTitle'] = 'Top 100 Nintendo Switch games';
        $bindings['crumbNav'] = $this->viewBreadcrumbs->topRatedSubpage('All-time');

        return view('topRated.allTime', $bindings);
    }

    public function byYear($year)
    {
        $serviceGameRankYear = $this->getServiceGameRankYear();

        $allowedYears = $this->getServiceGameCalendar()->getAllowedYears();
        if (!in_array($year, $allowedYears)) {
            abort(404);
        }

        $bindings = [];

        $gamesList = $serviceGameRankYear->getList($year, 100);

        $bindings['TopRatedByYear'] = $gamesList;
        $bindings['GamesTableSort'] = "[5, 'desc']";
        $bindings['Year'] = $year;

        $bindings['TopTitle'] = 'Top 100 Nintendo Switch games - released in '.$year;
        $bindings['PageTitle'] = 'Top 100 Nintendo Switch games - released in '.$year;
        $bindings['crumbNav'] = $this->viewBreadcrumbs->topRatedSubpage($year);

        return view('topRated.byYear', $bindings);
    }

}
