<?php

namespace App\Http\Controllers\Staff\DataQuality;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;
use App\Traits\StaffView;

use App\Services\DataQuality\QualityStats;

class CategoryController extends Controller
{
    use SwitchServices;
    use StaffView;

    public function dashboard()
    {
        $bindings = $this->getBindingsDataQualitySubpage('Category dashboard');

        $serviceQualityStats = new QualityStats();
        $bindings['StatsCategories'] = $serviceQualityStats->getCategoryStats();

        return view('staff.data-quality.categories.dashboard', $bindings);
    }

    public function gamesWithCategories($year, $month)
    {
        $bindings = $this->getBindingsDataQualityCategorySubpage('Games with categories');

        $serviceQualityStats = new QualityStats();
        $bindings['GameList'] = $serviceQualityStats->getGamesWithCategory($year, $month);

        return view('staff.data-quality.categories.game-list', $bindings);
    }

    public function gamesWithoutCategories($year, $month)
    {
        $bindings = $this->getBindingsDataQualityCategorySubpage('Games without categories');

        $serviceQualityStats = new QualityStats();
        $bindings['GameList'] = $serviceQualityStats->getGamesWithoutCategory($year, $month);

        return view('staff.data-quality.categories.game-list', $bindings);
    }
}
