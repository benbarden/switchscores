<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SiteRequestData;

class WelcomeController extends Controller
{
    use SiteRequestData;

    public function show()
    {
        $serviceContainer = $this->getServiceContainer();
        $regionCode = $this->getRegionCode();

        $bindings = [];

        $serviceReviewLinks = $serviceContainer->getReviewLinkService();
        $serviceGameReleaseDate = $serviceContainer->getGameReleaseDateService();
        $serviceTopRated = $serviceContainer->getTopRatedService();
        $serviceGameRankAllTime = $serviceContainer->getGameRankAllTimeService();
        $serviceGameRankYear = $serviceContainer->getGameRankYearService();

        $thisYear = date('Y');
        $bindings['Year'] = $thisYear;
        $bindings['RecentWithGoodRanks'] = $serviceGameReleaseDate->getRecentWithGoodRanks($regionCode, 7, 42, 18);
        $bindings['ReviewList'] = $serviceReviewLinks->getLatestNaturalOrder(20);
        $bindings['NewReleases'] = $serviceGameReleaseDate->getReleased($regionCode, 20);
        $bindings['TopRatedAllTime'] = $serviceTopRated->getList($regionCode, 20);
        $bindings['TopRatedThisYear'] = $serviceGameRankYear->getList($thisYear, 20);

        // Quick stats
        $bindings['TotalReleasedGames'] = $serviceGameReleaseDate->countReleased($regionCode);
        $bindings['TotalRanked'] = $serviceGameRankAllTime->countRanked();
        $bindings['TotalReviews'] = $serviceReviewLinks->countActive();

        $bindings['TopTitle'] = 'Welcome';
        $bindings['PageTitle'] = 'World of Switch - Homepage';

        return view('welcome', $bindings);
    }
}
