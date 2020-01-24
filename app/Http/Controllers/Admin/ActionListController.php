<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller as Controller;

use App\SiteAlert;

use App\Traits\SwitchServices;

class ActionListController extends Controller
{
    use SwitchServices;

    public function developerMissing()
    {
        $serviceGameDeveloper = $this->getServiceGameDeveloper();

        $bindings = [];

        $bindings['TopTitle'] = 'Games with no developer - Action lists - Admin';
        $bindings['PageTitle'] = 'Games with no developer';

        $bindings['GameList'] = $serviceGameDeveloper->getGamesWithNoDeveloper();
        $bindings['jsInitialSort'] = "[ 0, 'asc']";

        return view('admin.action-lists.game-developers.list', $bindings);
    }

    public function newDeveloperToSet()
    {
        $serviceGameDeveloper = $this->getServiceGameDeveloper();

        $bindings = [];

        $bindings['TopTitle'] = 'New developer to set (old developer set) - Action lists - Admin';
        $bindings['PageTitle'] = 'New developer to set (old developer set)';

        $bindings['GameList'] = $serviceGameDeveloper->getNewDevelopersToSet();
        $bindings['jsInitialSort'] = "[ 0, 'asc']";

        return view('admin.action-lists.game-developers.list', $bindings);
    }

    public function oldDeveloperToClear()
    {
        $serviceGameDeveloper = $this->getServiceGameDeveloper();

        $bindings = [];

        $bindings['TopTitle'] = 'Old developer to clear (new developer set) - Action lists - Admin';
        $bindings['PageTitle'] = 'Old developer to clear (new developer set)';

        $bindings['GameList'] = $serviceGameDeveloper->getOldDevelopersToClear();
        $bindings['jsInitialSort'] = "[ 0, 'asc']";

        return view('admin.action-lists.game-developers.list', $bindings);
    }

    public function publisherMissing()
    {
        $serviceGamePublisher = $this->getServiceGamePublisher();

        $bindings = [];

        $bindings['TopTitle'] = 'Games with no publisher - Action lists - Admin';
        $bindings['PageTitle'] = 'Games with no publisher';

        $bindings['GameList'] = $serviceGamePublisher->getGamesWithNoPublisher();
        $bindings['jsInitialSort'] = "[ 0, 'asc']";

        return view('admin.action-lists.game-publishers.list', $bindings);
    }

    public function newPublisherToSet()
    {
        $serviceGamePublisher = $this->getServiceGamePublisher();

        $bindings = [];

        $bindings['TopTitle'] = 'New publisher to set (old publisher set) - Action lists - Admin';
        $bindings['PageTitle'] = 'New publisher to set (old publisher set)';

        $bindings['GameList'] = $serviceGamePublisher->getNewPublishersToSet();
        $bindings['jsInitialSort'] = "[ 0, 'asc']";

        return view('admin.action-lists.game-publishers.list', $bindings);
    }

    public function oldPublisherToClear()
    {
        $serviceGamePublisher = $this->getServiceGamePublisher();

        $bindings = [];

        $bindings['TopTitle'] = 'Old publisher to clear (new publisher set) - Action lists - Admin';
        $bindings['PageTitle'] = 'Old publisher to clear (new publisher set)';

        $bindings['GameList'] = $serviceGamePublisher->getOldPublishersToClear();
        $bindings['jsInitialSort'] = "[ 0, 'asc']";

        return view('admin.action-lists.game-publishers.list', $bindings);
    }

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