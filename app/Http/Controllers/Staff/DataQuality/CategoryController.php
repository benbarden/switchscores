<?php

namespace App\Http\Controllers\Staff\DataQuality;

use Illuminate\Routing\Controller as Controller;

use App\Services\DataQuality\QualityStats;

use App\Traits\SwitchServices;

class CategoryController extends Controller
{
    use SwitchServices;

    public function dashboard()
    {
        $serviceQualityStats = new QualityStats();

        $pageTitle = 'Category dashboard';

        $breadcrumbs = $this->getServiceViewHelperBreadcrumbs()->makeDataQualitySubPage($pageTitle);

        $bindings = $this->getServiceViewHelperBindings()
            ->setPageTitle($pageTitle)
            ->setTopTitleSuffix('Data quality')
            ->setBreadcrumbs($breadcrumbs)
            ->getBindings();

        // Categories
        $bindings['StatsCategories'] = $serviceQualityStats->getCategoryStats();

        return view('staff.data-quality.categories.dashboard', $bindings);
    }

    public function gamesWithCategories($year, $month)
    {
        $serviceQualityStats = new QualityStats();

        $pageTitle = 'Games with categories';

        $breadcrumbs = $this->getServiceViewHelperBreadcrumbs()->makeDataQualitySubPage($pageTitle);

        $bindings = $this->getServiceViewHelperBindings()
            ->setPageTitle($pageTitle)
            ->setTopTitleSuffix('Data quality')
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
            ->setTopTitleSuffix('Data quality')
            ->setBreadcrumbs($breadcrumbs)
            ->getBindings();

        // Primary types
        $bindings['GameList'] = $serviceQualityStats->getGamesWithoutCategory($year, $month);

        return view('staff.data-quality.categories.game-list', $bindings);
    }
}
