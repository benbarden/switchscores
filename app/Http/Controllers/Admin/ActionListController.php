<?php

namespace App\Http\Controllers\Admin;

use App\SiteAlert;
use Illuminate\Routing\Controller as Controller;

use App\Services\ServiceContainer;

class ActionListController extends Controller
{
    public function landing()
    {
        $bindings = [];
        $bindings['TopTitle'] = 'Action lists - Admin';
        $bindings['PageTitle'] = 'Action lists';

        return view('admin.action-lists.landing', $bindings);
    }

    private function getRegionCodeOverride()
    {
        $regionCode = \Request::get('regionCode');
        $regionOverride = \Request::get('regionOverride');
        if ($regionOverride) {
            $regionCode = $regionOverride;
        }

        return $regionCode;
    }

    public function developerMissing()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $serviceGameDeveloper = $serviceContainer->getGameDeveloperService();

        $regionCode = $this->getRegionCodeOverride();

        $bindings = [];

        $bindings['TopTitle'] = 'Games with no developer - Action lists - Admin';
        $bindings['PageTitle'] = 'Games with no developer';

        $bindings['GameList'] = $serviceGameDeveloper->getGamesWithNoDeveloper();
        $bindings['jsInitialSort'] = "[ 0, 'asc']";

        $bindings['RegionCode'] = $regionCode;

        return view('admin.action-lists.game-developers.list', $bindings);
    }

    public function newDeveloperToSet()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $serviceGameDeveloper = $serviceContainer->getGameDeveloperService();

        $regionCode = $this->getRegionCodeOverride();

        $bindings = [];

        $bindings['TopTitle'] = 'New developer to set (old developer set) - Action lists - Admin';
        $bindings['PageTitle'] = 'New developer to set (old developer set)';

        $bindings['GameList'] = $serviceGameDeveloper->getNewDevelopersToSet();
        $bindings['jsInitialSort'] = "[ 0, 'asc']";

        $bindings['RegionCode'] = $regionCode;

        return view('admin.action-lists.game-developers.list', $bindings);
    }

    public function oldDeveloperToClear()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $serviceGameDeveloper = $serviceContainer->getGameDeveloperService();

        $regionCode = $this->getRegionCodeOverride();

        $bindings = [];

        $bindings['TopTitle'] = 'Old developer to clear (new developer set) - Action lists - Admin';
        $bindings['PageTitle'] = 'Old developer to clear (new developer set)';

        $bindings['GameList'] = $serviceGameDeveloper->getOldDevelopersToClear();
        $bindings['jsInitialSort'] = "[ 0, 'asc']";

        $bindings['RegionCode'] = $regionCode;

        return view('admin.action-lists.game-developers.list', $bindings);
    }

    public function publisherMissing()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $serviceGamePublisher = $serviceContainer->getGamePublisherService();

        $regionCode = $this->getRegionCodeOverride();

        $bindings = [];

        $bindings['TopTitle'] = 'Games with no publisher - Action lists - Admin';
        $bindings['PageTitle'] = 'Games with no publisher';

        $bindings['GameList'] = $serviceGamePublisher->getGamesWithNoPublisher();
        $bindings['jsInitialSort'] = "[ 0, 'asc']";

        $bindings['RegionCode'] = $regionCode;

        return view('admin.action-lists.game-publishers.list', $bindings);
    }

    public function newPublisherToSet()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $serviceGamePublisher = $serviceContainer->getGamePublisherService();

        $regionCode = $this->getRegionCodeOverride();

        $bindings = [];

        $bindings['TopTitle'] = 'New publisher to set (old publisher set) - Action lists - Admin';
        $bindings['PageTitle'] = 'New publisher to set (old publisher set)';

        $bindings['GameList'] = $serviceGamePublisher->getNewPublishersToSet();
        $bindings['jsInitialSort'] = "[ 0, 'asc']";

        $bindings['RegionCode'] = $regionCode;

        return view('admin.action-lists.game-publishers.list', $bindings);
    }

    public function oldPublisherToClear()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $serviceGamePublisher = $serviceContainer->getGamePublisherService();

        $regionCode = $this->getRegionCodeOverride();

        $bindings = [];

        $bindings['TopTitle'] = 'Old publisher to clear (new publisher set) - Action lists - Admin';
        $bindings['PageTitle'] = 'Old publisher to clear (new publisher set)';

        $bindings['GameList'] = $serviceGamePublisher->getOldPublishersToClear();
        $bindings['jsInitialSort'] = "[ 0, 'asc']";

        $bindings['RegionCode'] = $regionCode;

        return view('admin.action-lists.game-publishers.list', $bindings);
    }

    public function noPrice()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $serviceGame = $serviceContainer->getGameService();

        $regionCode = $this->getRegionCodeOverride();

        $bindings = [];

        $bindings['TopTitle'] = 'Games without prices - Action lists - Admin';
        $bindings['PageTitle'] = 'Games without prices';

        $bindings['GameList'] = $serviceGame->getWithoutPrices();
        $bindings['jsInitialSort'] = "[ 0, 'asc']";

        $bindings['RegionCode'] = $regionCode;

        return view('admin.action-lists.game-prices.list', $bindings);
    }

    public function siteAlertErrors()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $serviceSiteAlert = $serviceContainer->getSiteAlertService();

        $regionCode = $this->getRegionCodeOverride();

        $bindings = [];

        $bindings['TopTitle'] = 'Site alerts: Errors - Action lists - Admin';
        $bindings['PageTitle'] = 'Site alerts: Errors';

        $bindings['ItemList'] = $serviceSiteAlert->getByType(SiteAlert::TYPE_ERROR);
        $bindings['jsInitialSort'] = "[ 0, 'desc']";

        $bindings['RegionCode'] = $regionCode;

        return view('admin.action-lists.site-alerts.list', $bindings);
    }
}