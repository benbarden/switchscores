<?php

namespace App\Http\Controllers\Staff\Games;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;

class DashboardController extends Controller
{
    use SwitchServices;

    public function show()
    {
        $pageTitle = 'Games dashboard';

        $serviceGame = $this->getServiceGame();
        $serviceGameReleaseDate = $this->getServiceGameReleaseDate();

        $bindings = [];

        // Games to release
        $actionListGamesForReleaseCount = $serviceGame->getActionListGamesForRelease();
        $bindings['GamesForReleaseCount'] = count($actionListGamesForReleaseCount);

        // Missing data
        $missingVideoUrl = $serviceGame->getByNullField('video_url');
        $missingAmazonUkLink = $serviceGame->getWithoutAmazonUkLink();
        $bindings['MissingVideoUrlCount'] = count($missingVideoUrl);
        $bindings['MissingAmazonUkLink'] = count($missingAmazonUkLink);

        // Stats
        $bindings['TotalGameCount'] = $serviceGame->getCount();
        $bindings['ReleasedGameCount'] = $serviceGameReleaseDate->countReleased();
        $bindings['UpcomingGameCount'] = $serviceGameReleaseDate->countUpcoming();
        $bindings['NoEshopEuropeLinkCount'] = $this->getServiceGameFilterList()->getGamesWithoutEshopEuropeFsId()->count();

        $bindings['TopTitle'] = $pageTitle.' - Admin';
        $bindings['PageTitle'] = $pageTitle;

        return view('staff.games.dashboard', $bindings);
    }
}
