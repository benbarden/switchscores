<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as Controller;

use App\Services\ServiceContainer;

class WelcomeController extends Controller
{
    public function show()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $regionCode = \Request::get('regionCode');

        $bindings = [];

        $serviceReviewLinks = $serviceContainer->getReviewLinkService();
        $serviceGameReleaseDate = $serviceContainer->getGameReleaseDateService();
        $serviceTopRated = $serviceContainer->getTopRatedService();

        $bindings['ReviewList'] = $serviceReviewLinks->getLatestNaturalOrder(20);
        $bindings['NewReleases'] = $serviceGameReleaseDate->getReleased($regionCode, 20);
        $bindings['TopRatedAllTime'] = $serviceTopRated->getList($regionCode, 20);

        // Quick stats
        $bindings['TotalReleasedGames'] = $serviceGameReleaseDate->countReleased($regionCode);
        $bindings['TotalRanked'] = $serviceTopRated->getCount($regionCode);
        $bindings['TotalReviews'] = $serviceReviewLinks->countActive();

        $bindings['TopTitle'] = 'Welcome';
        $bindings['PageTitle'] = 'World of Switch - Homepage';

        return view('welcome', $bindings);
    }
}
