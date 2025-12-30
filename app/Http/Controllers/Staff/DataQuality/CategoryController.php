<?php

namespace App\Http\Controllers\Staff\DataQuality;

use Illuminate\Routing\Controller as Controller;

use App\Domain\View\Breadcrumbs\StaffBreadcrumbs;
use App\Domain\View\PageBuilders\StaffPageBuilder;

use App\Services\DataQuality\QualityStats;

class CategoryController extends Controller
{
    public function __construct(
        private StaffPageBuilder $pageBuilder,
    )
    {
    }

    public function dashboard()
    {
        $pageTitle = 'Category dashboard';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::dataQualitySubpage($pageTitle))->bindings;

        $serviceQualityStats = new QualityStats();
        $bindings['StatsCategories'] = $serviceQualityStats->getCategoryStats();

        return view('staff.data-quality.categories.dashboard', $bindings);
    }

    public function gamesWithCategories($year, $month)
    {
        $pageTitle = 'Games with categories';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::dataQualityCategoriesSubpage($pageTitle))->bindings;

        $serviceQualityStats = new QualityStats();
        $bindings['GameList'] = $serviceQualityStats->getGamesWithCategory($year, $month);

        return view('staff.data-quality.categories.game-list', $bindings);
    }

    public function gamesWithoutCategories($year, $month)
    {
        $pageTitle = 'Games without categories';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::dataQualityCategoriesSubpage($pageTitle))->bindings;

        $serviceQualityStats = new QualityStats();
        $bindings['GameList'] = $serviceQualityStats->getGamesWithoutCategory($year, $month);

        return view('staff.data-quality.categories.game-list', $bindings);
    }
}
