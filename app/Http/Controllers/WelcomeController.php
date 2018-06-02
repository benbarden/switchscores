<?php

namespace App\Http\Controllers;

use App\Services\NewsService;
use App\Services\ReviewLinkService;
use App\Services\GameReleaseDateService;
use App\Services\TopRatedService;
use Carbon\Carbon;

class WelcomeController extends BaseController
{
    public function show()
    {
        $bindings = [];

        $serviceReviewLinks = resolve('Services\ReviewLinkService');
        /* @var $serviceReviewLinks ReviewLinkService */
        $serviceGameReleaseDate = resolve('Services\GameReleaseDateService');
        /* @var $serviceGameReleaseDate GameReleaseDateService */
        $serviceTopRated = resolve('Services\TopRatedService');
        /* @var $serviceTopRated TopRatedService */

        $bindings['ReviewList'] = $serviceReviewLinks->getLatestNaturalOrder(5);
        $bindings['NewReleases'] = $serviceGameReleaseDate->getReleased($this->region, 15);
        $bindings['TopRatedAllTime'] = $serviceTopRated->getList($this->region, 15);

        // Charts
        $chartsDateService = resolve('Services\ChartsDateService');
        $bindings['ChartsLatestEu'] = $chartsDateService->getDateList('eu', 1);
        $bindings['ChartsLatestUs'] = $chartsDateService->getDateList('us', 1);

        // Quick stats
        $bindings['TotalReleasedGames'] = $serviceGameReleaseDate->countReleased($this->region);
        $bindings['TotalUpcomingGames'] = $serviceGameReleaseDate->countUpcoming($this->region);
        $bindings['TotalReviews'] = $serviceReviewLinks->countActive();

        $bindings['TopTitle'] = 'Welcome';
        $bindings['PageTitle'] = 'World of Switch - Homepage';

        return view('welcome', $bindings);
    }
}
