<?php

namespace App\Http\Controllers\DeveloperHub;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;
use App\Traits\AuthUser;

class CustomToolsController extends Controller
{
    use SwitchServices;
    use AuthUser;

    public function upcomingGamesSwitchWeekly()
    {
        $bindings = [];

        $pageTitle = 'Upcoming games (Switch Weekly)';

        $upcomingGames = $this->getServiceGameReleaseDate()->getUpcomingSwitchWeekly(7);

        $bindings['UpcomingGames'] = $upcomingGames;

        $bindings['TopTitle'] = $pageTitle;
        $bindings['PageTitle'] = $pageTitle;

        return view('developer-hub.custom-tools.upcoming-games-switch-weekly', $bindings);
    }
}
