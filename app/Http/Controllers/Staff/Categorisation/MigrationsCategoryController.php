<?php

namespace App\Http\Controllers\Staff\Categorisation;

use Illuminate\Routing\Controller as Controller;

use App\Domain\ViewBreadcrumbs\Staff as Breadcrumbs;

use App\Traits\SwitchServices;
use App\Traits\StaffView;

use App\Services\Migrations\Category as MigrationsCategory;

class MigrationsCategoryController extends Controller
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

    public function allGamesWithNoCategory()
    {
        $bindings = $this->getBindings('No category - with eShop data');
        $this->setTableSort("[ 3, 'asc']");
        $bindings['crumbNav'] = $this->viewBreadcrumbs->categorisationSubpage('No category - with eShop data');

        $serviceMigrationsCategory = new MigrationsCategory();

        $bindings['GameList'] = $serviceMigrationsCategory->getGamesWithEshopDataAndNoCategory();

        $bindings['CustomHeader'] = 'Genres';
        $bindings['ListMode'] = 'category-migration';

        return view('staff.games.list.standard-view', $bindings);
    }
}