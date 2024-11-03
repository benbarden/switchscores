<?php

namespace App\Http\Controllers\User;

use Illuminate\Routing\Controller as Controller;

use App\Domain\GameLists\DbQueries as GameListsDbQueries;

class DevelopersController extends Controller
{
    public function __construct(
        private GameListsDbQueries $dbGameLists
    )
    {
    }

    public function index()
    {
        $pageTitle = 'Developers';
        $breadcrumbs = resolve('View/Breadcrumbs/Member')->topLevelPage($pageTitle);
        $bindings = resolve('View/Bindings/Member')->setBreadcrumbs($breadcrumbs)->generateMember($pageTitle);

        return view('user.developers.index', $bindings);
    }

    public function switchWeekly()
    {
        $pageTitle = 'Upcoming games (Switch Weekly)';
        $breadcrumbs = resolve('View/Breadcrumbs/Member')->developersSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Member')->setBreadcrumbs($breadcrumbs)->generateMember($pageTitle);

        $upcomingGames = $this->dbGameLists->getUpcomingSwitchWeekly(7);

        $bindings['UpcomingGames'] = $upcomingGames;

        return view('user.developers.switch-weekly', $bindings);
    }
}
