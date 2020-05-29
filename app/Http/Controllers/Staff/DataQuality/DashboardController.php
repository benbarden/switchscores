<?php

namespace App\Http\Controllers\Staff\DataQuality;

use Illuminate\Routing\Controller as Controller;

use App\Services\DataQuality\QualityStats;

use App\Traits\SwitchServices;

class DashboardController extends Controller
{
    use SwitchServices;

    public function show($year = null, $month = null)
    {
        $serviceQualityStats = new QualityStats();

        $pageTitle = 'Data quality dashboard';

        $breadcrumbs = $this->getServiceViewHelperBreadcrumbs()->makeStaffDashboard($pageTitle);

        $bindings = $this->getServiceViewHelperBindings()
            ->setPageTitle($pageTitle)
            ->setTopTitlePrefix('Data quality')
            ->setBreadcrumbs($breadcrumbs)
            ->getBindings();

        // Primary types
        $bindings['StatsPrimaryTypes'] = $serviceQualityStats->getPrimaryTypeStats();

        return view('staff.data-quality.dashboard', $bindings);
    }

    public function gamesWithPrimaryTypes($year, $month)
    {
        $serviceQualityStats = new QualityStats();

        $pageTitle = 'Games with primary types';

        $breadcrumbs = $this->getServiceViewHelperBreadcrumbs()->makeDataQualitySubPage($pageTitle);

        $bindings = $this->getServiceViewHelperBindings()
            ->setPageTitle($pageTitle)
            ->setTopTitlePrefix('Data quality')
            ->setBreadcrumbs($breadcrumbs)
            ->getBindings();

        // Primary types
        $bindings['GameList'] = $serviceQualityStats->getGamesWithPrimaryType($year, $month);

        return view('staff.data-quality.primary-types.game-list', $bindings);
    }

    public function gamesWithoutPrimaryTypes($year, $month)
    {
        $serviceQualityStats = new QualityStats();

        $pageTitle = 'Games without primary types';

        $breadcrumbs = $this->getServiceViewHelperBreadcrumbs()->makeDataQualitySubPage($pageTitle);

        $bindings = $this->getServiceViewHelperBindings()
            ->setPageTitle($pageTitle)
            ->setTopTitlePrefix('Data quality')
            ->setBreadcrumbs($breadcrumbs)
            ->getBindings();

        // Primary types
        $bindings['GameList'] = $serviceQualityStats->getGamesWithoutPrimaryType($year, $month);

        return view('staff.data-quality.primary-types.game-list', $bindings);
    }
}
