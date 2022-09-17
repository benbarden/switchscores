<?php

namespace App\Http\Controllers\Staff\Partners;

use Illuminate\Routing\Controller as Controller;

use App\Domain\GameDeveloper\DbQueries as GameDeveloperDbQueries;
use App\Domain\GamePublisher\DbQueries as GamePublisherDbQueries;

use App\Traits\SwitchServices;
use App\Traits\StaffView;

class DataCleanupController extends Controller
{
    use SwitchServices;
    use StaffView;

    private $dbGameDeveloper;
    private $dbGamePublisher;

    public function __construct(
        GameDeveloperDbQueries $dbGameDeveloper,
        GamePublisherDbQueries $dbGamePublisher
    )
    {
        $this->dbGameDeveloper = $dbGameDeveloper;
        $this->dbGamePublisher = $dbGamePublisher;
    }

    public function gamesWithMissingDeveloper()
    {
        $bindings = $this->getBindingsPartnersSubpage('Games with missing developer');

        $bindings['ItemList'] = $this->dbGameDeveloper->getGamesWithNoDeveloper();

        return view('staff.partners.data-cleanup.games-with-missing-developer', $bindings);
    }

    public function gamesWithMissingPublisher()
    {
        $bindings = $this->getBindingsPartnersSubpage('Games with missing publisher');

        $bindings['ItemList'] = $this->dbGamePublisher->getGamesWithNoPublisher();

        return view('staff.partners.data-cleanup.games-with-missing-publisher', $bindings);
    }
}
