<?php

namespace App\Http\Controllers\Staff\DataQuality;

use Illuminate\Routing\Controller as Controller;

use App\Domain\ViewBreadcrumbs\Staff as Breadcrumbs;

use App\Traits\SwitchServices;
use App\Traits\StaffView;

use App\Services\DataQuality\QualityStats;

class CategoryController extends Controller
{
    use SwitchServices;
    use StaffView;

    protected $viewBreadcrumbs;

    public function __construct(
        Breadcrumbs $viewBreadcrumbs
    )
    {
        $this->viewBreadcrumbs = $viewBreadcrumbs;
    }

    public function dashboard()
    {
        $bindings = $this->getBindings('Category dashboard');
        $bindings['crumbNav'] = $this->viewBreadcrumbs->dataQualitySubpage('Category dashboard');

        $serviceQualityStats = new QualityStats();
        $bindings['StatsCategories'] = $serviceQualityStats->getCategoryStats();

        return view('staff.data-quality.categories.dashboard', $bindings);
    }

    public function gamesWithCategories($year, $month)
    {
        $bindings = $this->getBindings('Games with categories');
        $bindings['crumbNav'] = $this->viewBreadcrumbs->dataQualityCategoriesSubpage('Games with categories');

        $serviceQualityStats = new QualityStats();
        $bindings['GameList'] = $serviceQualityStats->getGamesWithCategory($year, $month);

        return view('staff.data-quality.categories.game-list', $bindings);
    }

    public function gamesWithoutCategories($year, $month)
    {
        $bindings = $this->getBindings('Games without categories');
        $bindings['crumbNav'] = $this->viewBreadcrumbs->dataQualityCategoriesSubpage('Games without categories');

        $serviceQualityStats = new QualityStats();
        $bindings['GameList'] = $serviceQualityStats->getGamesWithoutCategory($year, $month);

        return view('staff.data-quality.categories.game-list', $bindings);
    }
}
