<?php

namespace App\Http\Controllers\Staff\DataQuality;

use Illuminate\Routing\Controller as Controller;

use App\Services\DataQuality\QualityStats;

use App\Traits\SwitchServices;

class CategoryController extends Controller
{
    use SwitchServices;

    private function getListBindings($pageTitle)
    {
        $breadcrumbs = $this->getServiceViewHelperStaffBreadcrumbs()->makeDataQualitySubPage($pageTitle);

        $bindings = $this->getServiceViewHelperBindings()
            ->setPageTitle($pageTitle)
            ->setTopTitlePrefix('Data quality')
            ->setBreadcrumbs($breadcrumbs);

        return $bindings->getBindings();
    }

    public function dashboard()
    {
        $serviceQualityStats = new QualityStats();

        $pageTitle = 'Category dashboard';

        $bindings = $this->getListBindings($pageTitle);

        $bindings['StatsCategories'] = $serviceQualityStats->getCategoryStats();

        return view('staff.data-quality.categories.dashboard', $bindings);
    }

    public function gamesWithCategories($year, $month)
    {
        $serviceQualityStats = new QualityStats();

        $pageTitle = 'Games with categories';

        $bindings = $this->getListBindings($pageTitle);

        $bindings['GameList'] = $serviceQualityStats->getGamesWithCategory($year, $month);

        return view('staff.data-quality.categories.game-list', $bindings);
    }

    public function gamesWithoutCategories($year, $month)
    {
        $serviceQualityStats = new QualityStats();

        $pageTitle = 'Games without categories';

        $bindings = $this->getListBindings($pageTitle);

        $bindings['GameList'] = $serviceQualityStats->getGamesWithoutCategory($year, $month);

        return view('staff.data-quality.categories.game-list', $bindings);
    }
}
