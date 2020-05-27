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

        $totalGameCount = $this->getServiceGame()->getCount();

        // Primary types
        $bindings['StatsPrimaryTypes'] = $serviceQualityStats->getPrimaryTypeStats();

        return view('staff.data-quality.dashboard', $bindings);
    }
}
