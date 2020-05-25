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

        // ***** Primary types ***** //
        // All-time stats
        $statsWithPrimaryType = $serviceQualityStats->countGamesWithPrimaryType();
        $statsWithoutPrimaryType = $serviceQualityStats->countGamesWithoutPrimaryType();
        $bindings['StatsWithPrimaryType'] = $statsWithPrimaryType;
        $bindings['StatsWithoutPrimaryType'] = $statsWithoutPrimaryType;
        $statsPrimaryTypeProgress = ($statsWithPrimaryType) / $totalGameCount * 100;
        $bindings['StatsPrimaryTypeProgress'] = round($statsPrimaryTypeProgress, 2);

        // Monthly stats
        if ($year && $month) {
            $bindings['CurrentPeriodDesc'] = $year.'-'.$month;
            $bindings['PeriodWithPrimaryType'] = $serviceQualityStats->countGamesWithPrimaryTypeByYearMonth($year, $month);
            $bindings['PeriodWithoutPrimaryType'] = $serviceQualityStats->countGamesWithoutPrimaryTypeByYearMonth($year, $month);
        }

        // Period list
        $periodList = $this->getServiceGameCalendar()->getAllowedDates();
        $bindings['PeriodList'] = $periodList;

        return view('staff.data-quality.dashboard', $bindings);
    }
}
