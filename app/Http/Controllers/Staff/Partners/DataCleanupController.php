<?php

namespace App\Http\Controllers\Staff\Partners;

use Illuminate\Routing\Controller as Controller;

use App\Domain\View\Breadcrumbs\StaffBreadcrumbs;
use App\Domain\View\PageBuilders\StaffPageBuilder;

use App\Domain\GameDeveloper\DbQueries as GameDeveloperDbQueries;
use App\Domain\GamePublisher\DbQueries as GamePublisherDbQueries;

class DataCleanupController extends Controller
{
    public function __construct(
        private StaffPageBuilder $pageBuilder,
        private GameDeveloperDbQueries $dbGameDeveloper,
        private GamePublisherDbQueries $dbGamePublisher
    )
    {
    }

    public function gamesWithMissingDeveloper()
    {
        $pageTitle = 'Games with missing developer';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::gamesCompaniesSubpage($pageTitle))->bindings;

        $bindings['ItemList'] = $this->dbGameDeveloper->getGamesWithNoDeveloper();

        return view('staff.partners.data-cleanup.games-with-missing-developer', $bindings);
    }

    public function gamesWithMissingPublisher()
    {
        $pageTitle = 'Games with missing publisher';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::gamesCompaniesSubpage($pageTitle))->bindings;

        $bindings['ItemList'] = $this->dbGamePublisher->getGamesWithNoPublisher();

        return view('staff.partners.data-cleanup.games-with-missing-publisher', $bindings);
    }
}
