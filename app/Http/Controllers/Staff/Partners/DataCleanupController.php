<?php

namespace App\Http\Controllers\Staff\Partners;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;
use App\Traits\StaffView;

class DataCleanupController extends Controller
{
    use SwitchServices;
    use StaffView;

    public function gamesWithMissingDeveloper()
    {
        $bindings = $this->getBindingsPartnersSubpage('Games with missing developer');

        $bindings['ItemList'] = $this->getServiceGameDeveloper()->getGamesWithNoDeveloper();

        return view('staff.partners.data-cleanup.games-with-missing-developer', $bindings);
    }

    public function gamesWithMissingPublisher()
    {
        $bindings = $this->getBindingsPartnersSubpage('Games with missing publisher');

        $bindings['ItemList'] = $this->getServiceGamePublisher()->getGamesWithNoPublisher();

        return view('staff.partners.data-cleanup.games-with-missing-publisher', $bindings);
    }
}
