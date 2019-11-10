<?php

namespace App\Http\Controllers\Staff\Wikipedia;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SiteRequestData;
use App\Traits\WosServices;

class DashboardController extends Controller
{
    use WosServices;
    use SiteRequestData;

    public function show()
    {
        $bindings = [];

        $serviceFeedItemGame = $this->getServiceFeedItemGame();

        $pendingFeedGameItems = $serviceFeedItemGame->getPending();
        $bindings['PendingWikiUpdateCount'] = count($pendingFeedGameItems);

        $pageTitle = 'Wikipedia dashboard';

        $bindings['TopTitle'] = $pageTitle.' - Admin';
        $bindings['PageTitle'] = $pageTitle;

        return view('staff.wikipedia.dashboard', $bindings);
    }
}
