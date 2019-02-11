<?php

namespace App\Http\Controllers\Admin;

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

    public function developerNotSet()
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

    public function oldDevelopersToMigrate()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $serviceGameDeveloper = $serviceContainer->getGameDeveloperService();

        $regionCode = $this->getRegionCodeOverride();

        $bindings = [];

        $bindings['TopTitle'] = 'Old developers to migrate - Action lists - Admin';
        $bindings['PageTitle'] = 'Old developers to migrate';

        $bindings['GameList'] = $serviceGameDeveloper->getOldDevelopersToMigrate();
        $bindings['jsInitialSort'] = "[ 0, 'asc']";

        $bindings['RegionCode'] = $regionCode;

        return view('admin.action-lists.game-developers.list', $bindings);
    }
}