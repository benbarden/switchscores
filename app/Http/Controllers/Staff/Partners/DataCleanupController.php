<?php

namespace App\Http\Controllers\Staff\Partners;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;

class DataCleanupController extends Controller
{
    use SwitchServices;

    public function gamesWithMissingDeveloper()
    {
        $serviceGameDeveloper = $this->getServiceGameDeveloper();

        $pageTitle = 'Games with missing developer';

        $bindings = [];

        $bindings['PageTitle'] = $pageTitle;
        $bindings['TopTitle'] = $pageTitle.' - Partners - Staff';

        $bindings['ItemList'] = $serviceGameDeveloper->getGamesWithNoDeveloper();
        $bindings['jsInitialSort'] = "[ 1, 'asc']";

        return view('staff.partners.data-cleanup.games-with-missing-developer', $bindings);
    }

    public function gamesWithMissingPublisher()
    {
        $serviceGamePublisher = $this->getServiceGamePublisher();

        $pageTitle = 'Games with missing publisher';

        $bindings = [];

        $bindings['PageTitle'] = $pageTitle;
        $bindings['TopTitle'] = $pageTitle.' - Partners - Staff';

        $bindings['ItemList'] = $serviceGamePublisher->getGamesWithNoPublisher();
        $bindings['jsInitialSort'] = "[ 1, 'asc']";

        return view('staff.partners.data-cleanup.games-with-missing-publisher', $bindings);
    }
}
