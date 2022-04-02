<?php

namespace App\Http\Controllers\DeveloperHub;

use Illuminate\Routing\Controller as Controller;

use App\Domain\GameLists\DbQueries as GameListsDbQueries;

class CustomToolsController extends Controller
{
    protected $dbGameLists;

    public function __construct(
        GameListsDbQueries $dbGameLists
    )
    {
        $this->dbGameLists = $dbGameLists;
    }

    public function upcomingGamesSwitchWeekly()
    {
        $bindings = [];

        $pageTitle = 'Upcoming games (Switch Weekly)';

        $upcomingGames = $this->dbGameLists->getUpcomingSwitchWeekly(7);

        $bindings['UpcomingGames'] = $upcomingGames;

        $bindings['TopTitle'] = $pageTitle;
        $bindings['PageTitle'] = $pageTitle;

        return view('developer-hub.custom-tools.upcoming-games-switch-weekly', $bindings);
    }
}
