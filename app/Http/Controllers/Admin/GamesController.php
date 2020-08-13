<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;
use App\Traits\AuthUser;

class GamesController extends Controller
{
    use SwitchServices;
    use AuthUser;

    public function showList($report = null)
    {
        $serviceGame = $this->getServiceGame();
        $serviceGameReleaseDate = $this->getServiceGameReleaseDate();
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
                // Developers and Publishers
                case 'no-publisher-set':
                    $gameList = $serviceGamePublisher->getGamesWithNoPublisher();
                    $jsInitialSort = "[ 0, 'desc']";
                    break;
                // Missing data
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