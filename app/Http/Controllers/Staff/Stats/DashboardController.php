<?php

namespace App\Http\Controllers\Staff\Stats;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SiteRequestData;
use App\Traits\WosServices;

class DashboardController extends Controller
{
    use WosServices;
    use SiteRequestData;

    public function show()
    {
        $pageTitle = 'Stats dashboard';

        $regionCode = $this->getRegionCode();

        $serviceGame = $this->getServiceGame();
        $serviceGameReleaseDate = $this->getServiceGameReleaseDate();

        $bindings = [];

        $bindings['TopTitle'] = $pageTitle.' - Admin';
        $bindings['PageTitle'] = $pageTitle;

        $bindings['TotalGameCount'] = $serviceGame->getCount();
        $bindings['ReleasedGameCount'] = $serviceGameReleaseDate->countReleased($regionCode);
        $bindings['UpcomingGameCount'] = $serviceGameReleaseDate->countUpcoming($regionCode);

        return view('staff.stats.dashboard', $bindings);
    }
}
