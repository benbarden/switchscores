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

        $feedItemsAllPending = $serviceFeedItemGame->getPending();
        $feedItemsPendingNoGameId = $serviceFeedItemGame->getPendingNoGameId();
        $feedItemsPendingWithGameId = $serviceFeedItemGame->getPendingWithGameId();
        $feedItemsComplete = $serviceFeedItemGame->getComplete();
        $feedItemsInactive = $serviceFeedItemGame->getInactive();
        $bindings['WikiUpdatesAllPendingCount'] = count($feedItemsAllPending);
        $bindings['WikiUpdatesNoGameIdCount'] = count($feedItemsPendingNoGameId);
        $bindings['WikiUpdatesWithGameIdCount'] = count($feedItemsPendingWithGameId);
        $bindings['WikiUpdatesCompleteCount'] = count($feedItemsComplete);
        $bindings['WikiUpdatesInactiveCount'] = count($feedItemsInactive);

        $pageTitle = 'Wikipedia dashboard';

        $bindings['TopTitle'] = $pageTitle.' - Admin';
        $bindings['PageTitle'] = $pageTitle;

        return view('staff.wikipedia.dashboard', $bindings);
    }
}
