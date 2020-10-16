<?php

namespace App\Http\Controllers\Staff\Partners;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;

class DataCleanupController extends Controller
{
    use SwitchServices;

    private function getListBindings($pageTitle, $tableSort = '')
    {
        $breadcrumbs = $this->getServiceViewHelperStaffBreadcrumbs()->makePartnersSubPage($pageTitle);

        $bindings = $this->getServiceViewHelperBindings()
            ->setPageTitle($pageTitle)
            ->setTopTitlePrefix('Partners')
            ->setBreadcrumbs($breadcrumbs);

        if ($tableSort) {
            $bindings = $bindings->setDatatablesSort($tableSort);
        } else {
            $bindings = $bindings->setDatatablesSortDefault();
        }

        return $bindings->getBindings();
    }

    public function gamesWithMissingDeveloper()
    {
        $serviceGameDeveloper = $this->getServiceGameDeveloper();

        $pageTitle = 'Games with missing developer';

        $bindings = $this->getListBindings($pageTitle, "[ 0, 'asc']");

        $bindings['ItemList'] = $serviceGameDeveloper->getGamesWithNoDeveloper();

        return view('staff.partners.data-cleanup.games-with-missing-developer', $bindings);
    }

    public function gamesWithMissingPublisher()
    {
        $serviceGamePublisher = $this->getServiceGamePublisher();

        $pageTitle = 'Games with missing publisher';

        $bindings = $this->getListBindings($pageTitle, "[ 0, 'asc']");

        $bindings['ItemList'] = $serviceGamePublisher->getGamesWithNoPublisher();

        return view('staff.partners.data-cleanup.games-with-missing-publisher', $bindings);
    }
}
