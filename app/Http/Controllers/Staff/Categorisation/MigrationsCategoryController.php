<?php

namespace App\Http\Controllers\Staff\Categorisation;

use App\Services\Migrations\Category as MigrationsCategory;
use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;

class MigrationsCategoryController extends Controller
{
    use SwitchServices;

    private function getListBindings($pageTitle, $tableSort = '')
    {
        $breadcrumbs = $this->getServiceViewHelperStaffBreadcrumbs()->makeCategorisationSubPage($pageTitle);

        $bindings = $this->getServiceViewHelperBindings()
            ->setPageTitle($pageTitle)
            ->setTopTitlePrefix('Categorisation - Migrations')
            ->setBreadcrumbs($breadcrumbs);

        if ($tableSort) {
            $bindings = $bindings->setDatatablesSort($tableSort);
        } else {
            $bindings = $bindings->setDatatablesSortDefault();
        }

        return $bindings->getBindings();
    }

    public function gamesWithOneGenre()
    {
        $bindings = $this->getListBindings('No category: Games with one genre and no category', "[ 3, 'asc']");

        $serviceMigrationsCategory = new MigrationsCategory();

        $bindings['GameList'] = $serviceMigrationsCategory->getGamesWithOneGenre();

        $bindings['CustomHeader'] = 'Genres';
        $bindings['ListMode'] = 'category-migration';

        return view('staff.games.list.standard-view', $bindings);
    }

    public function gamesWithNamedGenreAndOneOther($genre)
    {
        $bindings = $this->getListBindings('No category: Games with '.$genre.' and one other', "[ 3, 'asc']");

        $serviceMigrationsCategory = new MigrationsCategory();

        $bindings['GameList'] = $serviceMigrationsCategory->getGamesWithNamedGenreAndOneOther('Puzzle');

        $bindings['CustomHeader'] = 'Genres';
        $bindings['ListMode'] = 'category-migration';

        return view('staff.games.list.standard-view', $bindings);
    }

    public function allGamesWithNoCategory()
    {
        $bindings = $this->getListBindings('All games with no category', "[ 3, 'asc']");

        $serviceMigrationsCategory = new MigrationsCategory();

        $bindings['GameList'] = $serviceMigrationsCategory->getGamesWithEshopDataAndNoCategory();

        $bindings['CustomHeader'] = 'Genres';
        $bindings['ListMode'] = 'category-migration';

        return view('staff.games.list.standard-view', $bindings);
    }
}