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

        $serviceReviewLinks = resolve('Services\ReviewLinkService');
        /* @var $serviceReviewLinks ReviewLinkService */

        $bindings['ReviewList'] = $serviceReviewLinks->getLatestNaturalOrder(5);
        $bindings['NewReleases'] = $this->serviceGame->getListReleasedLastXDays(45, 15);
        $bindings['TopRatedAllTime'] = $this->serviceGame->getListTopRated(15);

        // Charts
        $chartsDateService = resolve('Services\ChartsDateService');
        $bindings['ChartsLatestEu'] = $chartsDateService->getDateList('eu', 1);
        $bindings['ChartsLatestUs'] = $chartsDateService->getDateList('us', 1);

        // Quick stats
        $bindings['TotalReleasedGames'] = $this->serviceGame->countReleased();
        $bindings['TotalUpcomingGames'] = $this->serviceGame->countUpcoming();
        $bindings['TotalReviews'] = $serviceReviewLinks->countActive();

        $bindings['TopTitle'] = 'Welcome';
        $bindings['PageTitle'] = 'World of Switch - Homepage';

        return view('welcome', $bindings);
    }
}
