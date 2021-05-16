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
        $bindings['Year'] = $thisYear;
        $bindings['TopRatedThisYear'] = $serviceGameRankYear->getList($thisYear, 15);
        $bindings['TopRated2020'] = $serviceGameRankYear->getList(2020, 15);
        $bindings['TopRatedAllTime'] = $serviceGameRankAllTime->getList(15);

        $bindings['TopTitle'] = 'Top Rated Nintendo Switch games';
        $bindings['PageTitle'] = 'Top Rated Nintendo Switch games';
        $bindings['crumbNav'] = $this->viewBreadcrumbs->topLevelPage('Top Rated');

        return view('topRated.landing', $bindings);
    }

    public function multiplayer()
    {
        $serviceGameRankAllTime = $this->getServiceGameRankAllTime();

        $bindings = [];

        $bindings['TopRatedMultiplayer'] = $serviceGameRankAllTime->getList(100, 'multiplayer');

        $bindings['TopTitle'] = 'Top Rated Nintendo Switch multiplayer games';
        $bindings['PageTitle'] = 'Top Rated Nintendo Switch multiplayer games';
        $bindings['crumbNav'] = $this->viewBreadcrumbs->topRatedSubpage('Multiplayer');

        return view('topRated.multiplayer', $bindings);
    }

    public function allTime()
    {
        $bindings = [];

        $serviceGameRankAllTime = $this->getServiceGameRankAllTime();
        $gamesList = $serviceGameRankAllTime->getList(100);

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
