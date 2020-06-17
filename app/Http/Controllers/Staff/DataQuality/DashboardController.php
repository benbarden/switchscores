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

        // Data integrity
        $bindings['DuplicateReviewsCount'] = count($serviceQualityStats->getDuplicateReviews());

        // Categories
        $bindings['StatsCategories'] = $serviceQualityStats->getCategoryStats();

        return view('staff.data-quality.dashboard', $bindings);
    }

    public function duplicateReviews()
    {
        $serviceQualityStats = new QualityStats();

        $pageTitle = 'Duplicate reviews';

        $breadcrumbs = $this->getServiceViewHelperBreadcrumbs()->makeDataQualitySubPage($pageTitle);

        $bindings = $this->getServiceViewHelperBindings()
            ->setPageTitle($pageTitle)
            ->setTopTitlePrefix($pageTitle)
            ->setBreadcrumbs($breadcrumbs)
            ->setDatatablesSortDefault()
            ->getBindings();

        $bindings['DuplicateReviews'] = $serviceQualityStats->getDuplicateReviews();

        return view('staff.data-quality.duplicate-reviews', $bindings);
    }

    public function gamesWithCategories($year, $month)
    {
        $serviceQualityStats = new QualityStats();

        $pageTitle = 'Games with categories';

        $breadcrumbs = $this->getServiceViewHelperBreadcrumbs()->makeDataQualitySubPage($pageTitle);

        $bindings = $this->getServiceViewHelperBindings()
            ->setPageTitle($pageTitle)
            ->setTopTitlePrefix('Data quality')
            ->setBreadcrumbs($breadcrumbs)
            ->getBindings();

        // Primary types
        $bindings['GameList'] = $serviceQualityStats->getGamesWithCategory($year, $month);

        return view('staff.data-quality.categories.game-list', $bindings);
    }

    public function gamesWithoutCategories($year, $month)
    {
        $serviceQualityStats = new QualityStats();

        $pageTitle = 'Games without categories';

        $breadcrumbs = $this->getServiceViewHelperBreadcrumbs()->makeDataQualitySubPage($pageTitle);

        $bindings = $this->getServiceViewHelperBindings()
            ->setPageTitle($pageTitle)
            ->setTopTitlePrefix('Data quality')
            ->setBreadcrumbs($breadcrumbs)
            ->getBindings();

        // Primary types
        $bindings['GameList'] = $serviceQualityStats->getGamesWithoutCategory($year, $month);

        return view('staff.data-quality.categories.game-list', $bindings);
    }
}
