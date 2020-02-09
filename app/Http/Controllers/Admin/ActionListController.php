<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller as Controller;

use App\SiteAlert;

use App\Traits\SwitchServices;

class ActionListController extends Controller
{
    use SwitchServices;

    public function noPrice()
    {
        $serviceGame = $this->getServiceGame();

        $bindings = [];

        $bindings['TopTitle'] = 'Games without prices - Action lists - Admin';
        $bindings['PageTitle'] = 'Games without prices';

        $bindings['GameList'] = $serviceGame->getWithoutPrices();
        $bindings['jsInitialSort'] = "[ 4, 'desc']";

        return view('admin.action-lists.game-prices.list', $bindings);
    }

    public function siteAlertErrors()
    {
        $serviceSiteAlert = $this->getServiceSiteAlert();

        $bindings = [];

        $bindings['TopTitle'] = 'Site alerts: Errors - Action lists - Admin';
        $bindings['PageTitle'] = 'Site alerts: Errors';

        $bindings['ItemList'] = $serviceSiteAlert->getByType(SiteAlert::TYPE_ERROR);
        $bindings['jsInitialSort'] = "[ 0, 'desc']";

        return view('admin.action-lists.site-alerts.list', $bindings);
    }
}