<?php

namespace App\Http\Controllers\Staff\Games;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SiteRequestData;
use App\Traits\WosServices;

class DashboardController extends Controller
{
    use WosServices;
    use SiteRequestData;

    public function show()
    {
        $pageTitle = 'Games dashboard';

        $regionCode = $this->getRegionCode();

        $serviceGame = $this->getServiceGame();
        $serviceGameReleaseDate = $this->getServiceGameReleaseDate();

        $bindings = [];

        // Games to release
        $actionListGamesForReleaseCountEu = $serviceGame->getActionListGamesForRelease('eu');
        $actionListGamesForReleaseCountUs = $serviceGame->getActionListGamesForRelease('us');
        $actionListGamesForReleaseCountJp = $serviceGame->getActionListGamesForRelease('jp');
        $bindings['GamesForReleaseCountEu'] = count($actionListGamesForReleaseCountEu);
        $bindings['GamesForReleaseCountUs'] = count($actionListGamesForReleaseCountUs);
        $bindings['GamesForReleaseCountJp'] = count($actionListGamesForReleaseCountJp);

        // Missing data
        $missingVideoUrl = $serviceGame->getByNullField('video_url', $regionCode);
        $missingAmazonUkLink = $serviceGame->getWithoutAmazonUkLink();
        $bindings['MissingVideoUrlCount'] = count($missingVideoUrl);
        $bindings['MissingAmazonUkLink'] = count($missingAmazonUkLink);

        // Stats
        $bindings['TotalGameCount'] = $serviceGame->getCount();
        $bindings['ReleasedGameCount'] = $serviceGameReleaseDate->countReleased($regionCode);
        $bindings['UpcomingGameCount'] = $serviceGameReleaseDate->countUpcoming($regionCode);
        $bindings['NoEshopEuropeLinkCount'] = $this->getServiceGameFilterList()->getGamesWithoutEshopEuropeFsId()->count();

        $bindings['TopTitle'] = $pageTitle.' - Admin';
        $bindings['PageTitle'] = $pageTitle;

        return view('staff.games.dashboard', $bindings);
    }
}
