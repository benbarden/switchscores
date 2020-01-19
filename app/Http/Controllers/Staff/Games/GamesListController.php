<?php

namespace App\Http\Controllers\Staff\Games;

use Illuminate\Routing\Controller as Controller;

use App\Traits\WosServices;
use App\Traits\SiteRequestData;

class GamesListController extends Controller
{
    use WosServices;
    use SiteRequestData;

    public function recentlyAdded()
    {
        $serviceGame = $this->getServiceGame();

        $bindings = [];

        $bindings['TopTitle'] = 'Games - Recently added';
        $bindings['PageTitle'] = 'Recently added';

        $bindings['GameList'] = $serviceGame->getRecentlyAdded(100);
        $bindings['jsInitialSort'] = "[ 0, 'desc']";

        return view('staff.games.list.standard-view', $bindings);
    }

    public function upcomingGames()
    {
        $regionCode = $this->getRegionCode();
        $serviceGameReleaseDate = $this->getServiceGameReleaseDate();

        $bindings = [];

        $bindings['TopTitle'] = 'Games - Upcoming';
        $bindings['PageTitle'] = 'Upcoming games';

        $bindings['GameList'] = $serviceGameReleaseDate->getUpcoming($regionCode);
        $bindings['jsInitialSort'] = "[ 3, 'asc']";

        return view('staff.games.list.standard-view', $bindings);
    }

    public function zzShowList($report = null)
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $regionCode = \Request::get('regionCode');
        $regionOverride = \Request::get('regionOverride');

        $serviceGame = $serviceContainer->getGameService();
        $serviceGameReleaseDate = $serviceContainer->getGameReleaseDateService();
        $serviceGameGenre = $serviceContainer->getGameGenreService();
        $serviceGameTag = $serviceContainer->getGameTagService();
        $serviceGameDeveloper = $serviceContainer->getGameDeveloperService();
        $serviceGamePublisher = $serviceContainer->getGamePublisherService();

        $bindings = [];

        $bindings['TopTitle'] = 'Admin - Games';
        $bindings['PageTitle'] = 'Games (Region: '.$regionCode.')';


        $bindings['LastAction'] = $lastAction = \Request::get('lastaction');

        $lastGameId = \Request::get('lastgameid');
        if ($lastGameId) {
            $lastGame = $serviceGame->find($lastGameId);
            if ($lastGame) {
                $bindings['LastGame'] = $lastGame;
            }
        }

        if ($report == null) {
            $bindings['ActiveNav'] = 'all';
            $gameList = $serviceGame->getAll($regionCode);
            $jsInitialSort = "[ 0, 'desc']";
        } else {
            $bindings['ActiveNav'] = $report;
            switch ($report) {
                case 'released':
                    $gameList = $serviceGameReleaseDate->getReleased($regionCode);
                    $jsInitialSort = "[ 3, 'desc'], [ 1, 'asc']";
                    break;
                case 'unreleased':
                    $gameList = $serviceGameReleaseDate->getUnreleased($regionCode);
                    $jsInitialSort = "[ 3, 'asc'], [ 1, 'asc']";
                    break;
                // Action lists
                case 'action-list-games-for-release':
                    if ($regionOverride) {
                        $regionCode = $regionOverride;
                        $bindings['PageTitle'] = 'Games (Region: '.$regionCode.')';
                    }
                    $gameList = $serviceGame->getActionListGamesForRelease($regionCode);
                    $jsInitialSort = "[ 3, 'asc'], [ 1, 'asc']";
                    break;
                // Upcoming
                case 'upcoming':
                    $gameList = $serviceGameReleaseDate->getUpcoming($regionCode);
                    $jsInitialSort = "[ 3, 'asc'], [ 1, 'asc']";
                    break;
                case 'upcoming-2018-with-dates':
                    $gameList = $serviceGameReleaseDate->getUpcomingYearWithDates(2018, $regionCode);
                    $jsInitialSort = "[ 3, 'asc'], [ 1, 'asc']";
                    break;
                case 'upcoming-beyond':
                    $gameList = $serviceGameReleaseDate->getUpcomingFuture(2018, $regionCode);
                    $jsInitialSort = "[ 3, 'asc'], [ 1, 'asc']";
                    break;
                // Developers and Publishers
                case 'game-developer-links':
                    $gameList = $serviceGameDeveloper->getGameDeveloperLinks();
                    $jsInitialSort = "[ 0, 'desc']";
                    break;
                case 'no-publisher-set':
                    $gameList = $serviceGamePublisher->getGamesWithNoPublisher();
                    $jsInitialSort = "[ 0, 'desc']";
                    break;
                case 'old-publishers-to-migrate':
                    $gameList = $serviceGamePublisher->getOldPublishersToMigrate();
                    $jsInitialSort = "[ 0, 'desc']";
                    break;
                case 'game-publisher-links':
                    $gameList = $serviceGamePublisher->getGamePublisherLinks();
                    $jsInitialSort = "[ 0, 'desc']";
                    break;
                // Missing data
                case 'no-genre':
                    $gameList = $serviceGameGenre->getGamesWithoutGenres($regionCode);
                    $jsInitialSort = "[ 0, 'desc']";
                    break;
                case 'no-eshop-europe-link':
                    $gameList = $serviceGame->getByNullField('eshop_europe_fs_id', $regionCode);
                    $jsInitialSort = "[ 3, 'asc'], [ 0, 'asc']";
                    break;
                case 'no-boxart':
                    $gameList = $serviceGame->getWithoutBoxart($regionCode);
                    $jsInitialSort = "[ 3, 'asc'], [ 0, 'asc']";
                    break;
                case 'no-video-url':
                    $gameList = $serviceGame->getByNullField('video_url', $regionCode);
                    $jsInitialSort = "[ 3, 'asc'], [ 0, 'asc']";
                    break;
                case 'no-amazon-uk-link':
                    $gameList = $serviceGame->getWithoutAmazonUkLink();
                    $jsInitialSort = "[ 0, 'asc']";
                    break;
                default:
                    abort(404);
            }
        }

        $bindings['GameList'] = $gameList;
        $bindings['jsInitialSort'] = $jsInitialSort;

        $bindings['RegionCode'] = $regionCode;

        return view('admin.games.list', $bindings);
    }

}