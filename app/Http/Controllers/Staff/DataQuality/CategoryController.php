<?php

namespace App\Http\Controllers\Staff\DataQuality;

use Illuminate\Routing\Controller as Controller;

use App\Services\DataQuality\QualityStats;

class CategoryController extends Controller
{
    public function dashboard()
    {
        $pageTitle = 'Category dashboard';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->dataQualitySubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $serviceQualityStats = new QualityStats();
        $bindings['StatsCategories'] = $serviceQualityStats->getCategoryStats();

        return view('staff.data-quality.categories.dashboard', $bindings);
    }

    public function gamesWithCategories($year, $month)
    {
        $pageTitle = 'Games with categories';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->dataQualityCategoriesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $serviceQualityStats = new QualityStats();
        $bindings['GameList'] = $serviceQualityStats->getGamesWithCategory($year, $month);

        return view('staff.data-quality.categories.game-list', $bindings);
    }

    public function gamesWithoutCategories($year, $month)
    {
        $pageTitle = 'Games without categories';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->dataQualityCategoriesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $serviceQualityStats = new QualityStats();
        $bindings['GameList'] = $serviceQualityStats->getGamesWithoutCategory($year, $month);

        return view('staff.data-quality.categories.game-list', $bindings);
    }
}
