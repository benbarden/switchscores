<?php

namespace App\Http\Controllers\Staff\Games;

use Illuminate\Routing\Controller as Controller;

use App\Category;
use App\GameSeries;

use App\Traits\SwitchServices;

class GamesListController extends Controller
{
    use SwitchServices;

    private function getListBindings($pageTitle, $tableSort = '')
    {
        $breadcrumbs = $this->getServiceViewHelperBreadcrumbs()->makeGamesSubPage($pageTitle);

        $bindings = $this->getServiceViewHelperBindings()
            ->setPageTitle($pageTitle)
            ->setTopTitlePrefix('Games')
            ->setBreadcrumbs($breadcrumbs);

        if ($tableSort) {
            $bindings = $bindings->setDatatablesSort($tableSort);
        } else {
            $bindings = $bindings->setDatatablesSortDefault();
        }

        return $bindings->getBindings();
    }

    public function recentlyAdded()
    {
        $bindings = $this->getListBindings('Recently added');

        $bindings['GameList'] = $this->getServiceGame()->getRecentlyAdded(100);

        return view('staff.games.list.standard-view', $bindings);
    }

    public function recentlyReleased()
    {
        $bindings = $this->getListBindings('Recently released', "[ 4, 'desc']");

        $bindings['GameList'] = $this->getServiceGame()->getRecentlyReleased(100);

        return view('staff.games.list.standard-view', $bindings);
    }

    public function upcomingGames()
    {
        $bindings = $this->getListBindings('Upcoming and unreleased', "[ 4, 'asc']");

        $bindings['GameList'] = $this->getServiceGameReleaseDate()->getAllUnreleased();

        return view('staff.games.list.standard-view', $bindings);
    }

    public function noNintendoCoUkLink()
    {
        $bindings = $this->getListBindings('No Nintendo.co.uk link');

        $bindings['GameList'] = $this->getServiceGame()->getWithNoNintendoCoUkLink();

        return view('staff.games.list.standard-view', $bindings);
    }

    public function byCategory(Category $category)
    {
        $bindings = $this->getListBindings('By category: '.$category->name, "[ 1, 'asc']");

        $bindings['GameList'] = $this->getServiceGame()->getByCategory($category);

        $bindings['CustomHeader'] = 'Category';
        $bindings['ListMode'] = 'by-category';

        return view('staff.games.list.standard-view', $bindings);
    }

    public function bySeries(GameSeries $gameSeries)
    {
        $bindings = $this->getListBindings('By series: '.$gameSeries->series, "[ 1, 'asc']");

        $bindings['GameList'] = $this->getServiceGame()->getBySeries($gameSeries);

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
                case 'no-publisher-set':
                    $gameList = $serviceGamePublisher->getGamesWithNoPublisher();
                    $jsInitialSort = "[ 0, 'desc']";
                    break;
                case 'old-publishers-to-migrate':
                    $gameList = $serviceGamePublisher->getOldPublishersToMigrate();
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