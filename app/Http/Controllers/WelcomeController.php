<?php

namespace App\Http\Controllers;

use Carbon\Carbon;

class WelcomeController extends BaseController
{
    public function show()
    {
        // Homepage content
        //$now = Carbon::now();
        //$newReleases = $this->serviceGame->getListReleasedByMonth($now->month);
        //$upcomingReleases = $this->serviceGame->getListUpcomingByMonth($now->month);

        $bindings = array();

        $bindings['NewReleases'] = $this->serviceGame->getListReleasedLastXDays(14);
        $bindings['UpcomingReleases'] = $this->serviceGame->getListUpcomingNextXDays(45);

        $chartsDateService = resolve('Services\ChartsDateService');
        $bindings['ChartsLatestEu'] = $chartsDateService->getDateList('eu', 1);
        $bindings['ChartsLatestUs'] = $chartsDateService->getDateList('us', 1);

        $bindings['TopTitle'] = 'Welcome to World of Switch';
        $bindings['PageTitle'] = 'World of Switch - Homepage';

        return view('welcome', $bindings);
    }
}
