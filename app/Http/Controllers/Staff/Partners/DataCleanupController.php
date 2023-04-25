<?php

namespace App\Http\Controllers\Staff\Partners;

use Illuminate\Routing\Controller as Controller;

use App\Domain\GameDeveloper\DbQueries as GameDeveloperDbQueries;
use App\Domain\GamePublisher\DbQueries as GamePublisherDbQueries;

class DataCleanupController extends Controller
{
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
        $pageTitle = 'Games with missing developer';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->partnersSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['ItemList'] = $this->dbGameDeveloper->getGamesWithNoDeveloper();

        return view('staff.partners.data-cleanup.games-with-missing-developer', $bindings);
    }

    public function gamesWithMissingPublisher()
    {
        $pageTitle = 'Games with missing publisher';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->partnersSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['ItemList'] = $this->dbGamePublisher->getGamesWithNoPublisher();

        return view('staff.partners.data-cleanup.games-with-missing-publisher', $bindings);
    }
}
