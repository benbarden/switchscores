<?php

namespace App\Http\Controllers\Staff\DataQuality;

use App\Services\DataQuality\QualityStats;
use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;

class DashboardController extends Controller
{
    use SwitchServices;

    public function show($year = null, $month = null)
    {
        $pageTitle = 'Data quality dashboard';

        $breadcrumbs = $this->getServiceViewHelperBreadcrumbs()->makeStaffDashboard($pageTitle);

        $bindings = $this->getServiceViewHelperBindings()
            ->setPageTitle($pageTitle)
            ->setTopTitlePrefix('Data quality')
            ->setBreadcrumbs($breadcrumbs)
            ->getBindings();

        return view('staff.data-quality.dashboard', $bindings);
    }
}
