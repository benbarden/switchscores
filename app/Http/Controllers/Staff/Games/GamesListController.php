<?php

namespace App\Http\Controllers\Staff\Games;

use Illuminate\Routing\Controller as Controller;

use App\GamePrimaryType;
use App\GameSeries;

use App\Traits\SwitchServices;

class GamesListController extends Controller
{
    use SwitchServices;

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
        $serviceGameReleaseDate = $this->getServiceGameReleaseDate();

        $bindings = [];

        $bindings['TopTitle'] = 'Games - Upcoming and unreleased';
        $bindings['PageTitle'] = 'Upcoming and unreleased games';

        $bindings['GameList'] = $serviceGameReleaseDate->getAllUnreleased();
        $bindings['jsInitialSort'] = "[ 3, 'asc']";

        return view('staff.games.list.standard-view', $bindings);
    }

    public function byPrimaryType(GamePrimaryType $primaryType)
    {
        $bindings = [];

        $bindings['TopTitle'] = 'Games - By primary type: '.$primaryType->primary_type;
        $bindings['PageTitle'] = 'Games - By primary type: '.$primaryType->primary_type;

        $bindings['GameList'] = $this->getServiceGame()->getByPrimaryType($primaryType);
        $bindings['jsInitialSort'] = "[ 1, 'asc']";

        $bindings['CustomHeader'] = 'Primary type';
        $bindings['ListMode'] = 'by-primary-type';

        return view('staff.games.list.standard-view', $bindings);
    }

    public function bySeries(GameSeries $gameSeries)
    {
        $bindings = [];

        $bindings['TopTitle'] = 'Games - By series: '.$gameSeries->series;
        $bindings['PageTitle'] = 'Games - By series: '.$gameSeries->series;

        $bindings['GameList'] = $this->getServiceGame()->getBySeries($gameSeries);
        $bindings['jsInitialSort'] = "[ 1, 'asc']";

        $bindings['CustomHeader'] = 'Series';
        $bindings['ListMode'] = 'by-series';

        return view('staff.games.list.standard-view', $bindings);
    }

    public function zzShowList($report = null)
    {
        $serviceGame = $this->getServiceGame();
        $serviceGameReleaseDate = $this->getServiceGameReleaseDate();
        $serviceGameGenre = $this->getServiceGameGenre();
        $serviceGameDeveloper = $this->getServiceGameDeveloper();
        $serviceGamePublisher = $this->getServiceGamePublisher();

        $bindings = [];

        $bindings['TopTitle'] = 'Admin - Games';
        $bindings['PageTitle'] = 'Games';

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
            $gameList = $serviceGame->getAll();
            $jsInitialSort = "[ 0, 'desc']";
        } else {
            $bindings['ActiveNav'] = $report;
            switch ($report) {
                case 'released':
                    $gameList = $serviceGameReleaseDate->getReleased();
                    $jsInitialSort = "[ 3, 'desc'], [ 1, 'asc']";
                    break;
                // Action lists
                case 'action-list-games-for-release':
                    $gameList = $serviceGame->getActionListGamesForRelease();
                    $jsInitialSort = "[ 3, 'asc'], [ 1, 'asc']";
                    break;
                // Upcoming
                case 'upcoming':
                    $gameList = $serviceGameReleaseDate->getUpcoming();
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
                    $gameList = $serviceGameGenre->getGamesWithoutGenres();
                    $jsInitialSort = "[ 0, 'desc']";
                    break;
                case 'no-eshop-europe-link':
                    $gameList = $serviceGame->getByNullField('eshop_europe_fs_id');
                    $jsInitialSort = "[ 3, 'asc'], [ 0, 'asc']";
                    break;
                case 'no-boxart':
                    $gameList = $serviceGame->getWithoutBoxart();
                    $jsInitialSort = "[ 3, 'asc'], [ 0, 'asc']";
                    break;
                case 'no-video-url':
                    $gameList = $serviceGame->getByNullField('video_url');
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

        return view('admin.games.list', $bindings);
    }

}