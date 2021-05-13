<?php

namespace App\Http\Controllers\Staff\Categorisation;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;
use App\Traits\StaffView;

use App\Services\Migrations\Category as MigrationsCategory;

class MigrationsCategoryController extends Controller
{
    use SwitchServices;
    use StaffView;

    public function allGamesWithNoCategory()
    {
        $bindings = $this->getBindingsCategorisationSubpage('No category - with eShop data', "[ 3, 'asc']");

        $serviceMigrationsCategory = new MigrationsCategory();

        $bindings['GameList'] = $serviceMigrationsCategory->getGamesWithEshopDataAndNoCategory();

        $bindings['CustomHeader'] = 'Genres';
        $bindings['ListMode'] = 'category-migration';

        return view('staff.games.list.standard-view', $bindings);
    }
}