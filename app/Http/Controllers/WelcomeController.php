<?php

namespace App\Http\Controllers;

use App\Services\NewsService;
use App\Services\ReviewLinkService;
use Carbon\Carbon;

class WelcomeController extends BaseController
{
    public function show()
    {
        $bindings = array();

        $reviewLinkService = resolve('Services\ReviewLinkService');
        /* @var $reviewLinkService ReviewLinkService */

        $bindings['NewReleases'] = $this->serviceGame->getListReleasedLastXDays(45, 15);
        $bindings['UpcomingReleases'] = $this->serviceGame->getListUpcomingNextXDays(45, 15);
        $bindings['TopRatedNewReleases'] = $this->serviceGame->getListTopRatedLastXDays(30, 10);

        $chartsDateService = resolve('Services\ChartsDateService');
        $bindings['ChartsLatestEu'] = $chartsDateService->getDateList('eu', 1);
        $bindings['ChartsLatestUs'] = $chartsDateService->getDateList('us', 1);

        $bindings['TotalReleasedGames'] = $this->serviceGame->countReleased();
        $bindings['TotalUpcomingGames'] = $this->serviceGame->countUpcoming();
        $bindings['TotalReviews'] = $reviewLinkService->countActive();

        $bindings['TopTitle'] = 'Welcome';
        $bindings['PageTitle'] = 'World of Switch - Homepage';

        return view('welcome', $bindings);
    }
}
