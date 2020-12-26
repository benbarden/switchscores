<?php

namespace App\Http\Controllers\Staff\Games;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;
use App\Traits\StaffView;

class DashboardController extends Controller
{
    use SwitchServices;
    use StaffView;

    public function show()
    {
        $bindings = $this->getBindingsDashboardGenericSubpage('Games dashboard');

        $serviceGameReleaseDate = $this->getServiceGameReleaseDate();

        // Games to release
        $actionListGamesForReleaseCount = $this->getServiceGame()->getActionListGamesForRelease();
        $bindings['GamesForReleaseCount'] = count($actionListGamesForReleaseCount);

        // Missing data
        $bindings['NoNintendoCoUkLinkCount'] = $this->getServiceGame()->getWithNoNintendoCoUkLink()->count();
        $bindings['BrokenNintendoCoUkLinkCount'] = $this->getServiceGame()->getWithBrokenNintendoCoUkLink()->count();
        $bindings['NoPriceCount'] = $this->getServiceGame()->countWithoutPrices();
        $bindings['MissingVideoUrlCount'] = $this->getServiceGame()->countWithNoVideoUrl();
        $bindings['MissingAmazonUkLink'] = $this->getServiceGame()->countWithNoAmazonUkLink();

        // Release date stats
        $bindings['TotalGameCount'] = $this->getServiceGame()->getCount();
        $bindings['ReleasedGameCount'] = $serviceGameReleaseDate->countReleased();
        $bindings['UpcomingGameCount'] = $serviceGameReleaseDate->countUpcoming();

        return view('staff.games.dashboard', $bindings);
    }
}
