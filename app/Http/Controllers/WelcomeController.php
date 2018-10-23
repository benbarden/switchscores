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
        $regionCode = \Request::get('regionCode');

        $bindings = [];

        $serviceReviewLinks = resolve('Services\ReviewLinkService');
        /* @var $serviceReviewLinks ReviewLinkService */
        $serviceGameReleaseDate = resolve('Services\GameReleaseDateService');
        /* @var $serviceGameReleaseDate GameReleaseDateService */
        $serviceTopRated = resolve('Services\TopRatedService');
        /* @var $serviceTopRated TopRatedService */

        $bindings['ReviewList'] = $serviceReviewLinks->getLatestNaturalOrder(20);
        $bindings['NewReleases'] = $serviceGameReleaseDate->getReleased($regionCode, 20);
        $bindings['TopRatedAllTime'] = $serviceTopRated->getList($regionCode, 20);

        // Charts
        $chartsDateService = resolve('Services\ChartsDateService');
        $bindings['ChartsLatestEu'] = $chartsDateService->getDateList('eu', 1);
        $bindings['ChartsLatestUs'] = $chartsDateService->getDateList('us', 1);

        // Quick stats
        $bindings['TotalReleasedGames'] = $serviceGameReleaseDate->countReleased($regionCode);
        $bindings['TotalUpcomingGames'] = $serviceGameReleaseDate->countUpcoming($regionCode);
        $bindings['TotalReviews'] = $serviceReviewLinks->countActive();

        $bindings['TopTitle'] = 'Welcome';
        $bindings['PageTitle'] = 'World of Switch - Homepage';

        return view('welcome', $bindings);
    }
}
