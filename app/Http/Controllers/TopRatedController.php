<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as Controller;

use App\Traits\WosServices;
use App\Traits\SiteRequestData;

class TopRatedController extends Controller
{
    use WosServices;
    use SiteRequestData;

    public function landing()
    {
        $serviceGameRankAllTime = $this->getServiceGameRankAllTime();
        $serviceGameRankYear = $this->getServiceGameRankYear();

        $bindings = [];

        $thisYear = date('Y');
        $bindings['Year'] = $thisYear;
        $bindings['TopRatedThisYear'] = $serviceGameRankYear->getList($thisYear, 15);
        $bindings['TopRated2019'] = $serviceGameRankYear->getList(2019, 15);
        $bindings['TopRatedAllTime'] = $serviceGameRankAllTime->getList(15);

        $bindings['TopTitle'] = 'Top Rated Nintendo Switch games';
        $bindings['PageTitle'] = 'Top Rated Nintendo Switch games';

        return view('topRated.landing', $bindings);
    }

    public function multiplayer()
    {
        $serviceGameRankAllTime = $this->getServiceGameRankAllTime();

        $bindings = [];

        $bindings['TopRatedMultiplayer'] = $serviceGameRankAllTime->getList(100, 'multiplayer');

        $bindings['TopTitle'] = 'Top Rated Nintendo Switch multiplayer games';
        $bindings['PageTitle'] = 'Top Rated Nintendo Switch multiplayer games';

        return view('topRated.multiplayer', $bindings);
    }

    public function allTime()
    {
        $bindings = [];

        $serviceGameRankAllTime = $this->getServiceGameRankAllTime();
        $gamesList = $serviceGameRankAllTime->getList(100);

        $bindings['TopRatedAllTime'] = $gamesList;

        $bindings['TopTitle'] = 'Nintendo Switch Top 100 games';
        $bindings['PageTitle'] = 'Top 100 Nintendo Switch games';

        return view('topRated.allTime', $bindings);
    }

    public function byYear($year)
    {
        $serviceGameRankYear = $this->getServiceGameRankYear();

        $allowedYears = [2017, 2018, 2019, 2020];
        if (!in_array($year, $allowedYears)) {
            abort(404);
        }

        $bindings = [];

        $gamesList = $serviceGameRankYear->getList($year, 100);

        $bindings['TopRatedByYear'] = $gamesList;
        $bindings['GamesTableSort'] = "[5, 'desc']";
        $bindings['Year'] = $year;

        $bindings['TopTitle'] = 'Nintendo Switch Top 100 games - released in '.$year;
        $bindings['PageTitle'] = 'Top 100 Nintendo Switch games - released in '.$year;

        return view('topRated.byYear', $bindings);
    }

}
