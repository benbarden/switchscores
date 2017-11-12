<?php

namespace App\Http\Controllers;

use App\Services\ActivityFeedService;
use App\Services\NewsService;
use Carbon\Carbon;

class WelcomeController extends BaseController
{
    public function show()
    {
        $bindings = array();

        /* @var $serviceActivityFeed ActivityFeedService */
        /*
        $serviceActivityFeed = resolve('Services\ActivityFeedService');
        $bindings['ActivityFeedList'] = $serviceActivityFeed->getAll();
        */

        $newsService = resolve('Services\NewsService');
        /* @var $newsService NewsService */
        $bindings['NewsList'] = $newsService->getAllWithLimit(10);

        $bindings['NewReleases'] = $this->serviceGame->getListReleasedLastXDays(45, 10);
        $bindings['UpcomingReleases'] = $this->serviceGame->getListUpcomingNextXDays(45, 10);
        $bindings['TopRatedNewReleases'] = $this->serviceGame->getListTopRatedLastXDays(30, 10);

        $chartsDateService = resolve('Services\ChartsDateService');
        $bindings['ChartsLatestEu'] = $chartsDateService->getDateList('eu', 1);
        $bindings['ChartsLatestUs'] = $chartsDateService->getDateList('us', 1);

        $bindings['TopTitle'] = 'Welcome to World of Switch';
        $bindings['PageTitle'] = 'World of Switch - Homepage';

        return view('welcome', $bindings);
    }
}
