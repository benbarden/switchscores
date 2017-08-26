<?php

namespace App\Http\Controllers;

use App\Services\ActivityFeedService;
use Carbon\Carbon;

class WelcomeController extends BaseController
{
    public function show()
    {
        $bindings = array();

        // Homepage content
        //$now = Carbon::now();
        //$newReleases = $this->serviceGame->getListReleasedByMonth($now->month);
        //$upcomingReleases = $this->serviceGame->getListUpcomingByMonth($now->month);

        $serviceActivityFeed = resolve('Services\ActivityFeedService');
        /* @var $serviceActivityFeed ActivityFeedService */
        $bindings['ActivityFeedList'] = $serviceActivityFeed->getAll();

        $bindings['NewReleases'] = $this->serviceGame->getListReleasedLastXDays(45, 8);
        $bindings['UpcomingReleases'] = $this->serviceGame->getListUpcomingNextXDays(45, 8);
        $bindings['TopRatedNewReleases'] = $this->serviceGame->getListTopRatedLastXDays(30, 10);

        $chartsDateService = resolve('Services\ChartsDateService');
        $bindings['ChartsLatestEu'] = $chartsDateService->getDateList('eu', 1);
        $bindings['ChartsLatestUs'] = $chartsDateService->getDateList('us', 1);

        $bindings['TopTitle'] = 'Welcome to World of Switch';
        $bindings['PageTitle'] = 'World of Switch - Homepage';

        return view('welcome', $bindings);
    }
}
