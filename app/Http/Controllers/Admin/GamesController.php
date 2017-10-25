<?php

namespace App\Http\Controllers\Admin;

use App\Events\GameCreated;
use Illuminate\Http\Request;

class GamesController extends \App\Http\Controllers\BaseController
{
    /**
     * @var array
     */
    private $validationRules = [
        'title' => 'required|max:255',
        'link_title' => 'required|max:100',
        'price_eshop' => 'max:6',
        'players' => 'max:10',
        'upcoming_date' => 'max:30',
        'developer' => 'max:100',
        'publisher' => 'max:100',
    ];

    public function showList($report = null)
    {
        $bindings = array();

        $bindings['TopTitle'] = 'Admin - Games';
        $bindings['PanelTitle'] = 'Games';

        if ($report == null) {
            $bindings['ActiveNav'] = 'all';
            $gameList = $this->serviceGame->getAll();
            $jsInitialSort = "[ 0, 'desc']";
        } else {
            $bindings['ActiveNav'] = $report;
            switch ($report) {
                case 'released':
                    $gameList = $this->serviceGame->getAllReleased();
                    $jsInitialSort = "[ 2, 'desc']";
                    break;
                case 'upcoming':
                    $gameList = $this->serviceGame->getAllUpcoming();
                    $jsInitialSort = "[ 2, 'asc'], [ 1, 'asc']";
                    break;
                case 'upcoming-tba':
                    $gameList = $this->serviceGame->getAllUpcomingTBA();
                    $jsInitialSort = "[ 2, 'asc'], [ 1, 'asc']";
                    break;
                case 'upcoming-q':
                    $gameList = $this->serviceGame->getAllUpcomingQs();
                    $jsInitialSort = "[ 2, 'asc'], [ 1, 'asc']";
                    break;
                case 'upcoming-x':
                    $gameList = $this->serviceGame->getAllUpcomingXs();
                    $jsInitialSort = "[ 2, 'asc'], [ 1, 'asc']";
                    break;
                case 'no-dev-or-pub':
                    $gameList = $this->serviceGame->getWithoutDevOrPub();
                    $jsInitialSort = "[ 2, 'asc'], [ 1, 'asc']";
                    break;
                case 'no-amazon-uk-link':
                    $gameList = $this->serviceGame->getWithoutAmazonUkLink();
                    $jsInitialSort = "[ 0, 'desc']";
                    break;
                case 'no-genre':
                    $gameList = $this->serviceGame->getGamesWithoutGenres();
                    $jsInitialSort = "[ 0, 'desc']";
                    break;
                default:
                    abort(404);
            }
        }

        $bindings['GameList'] = $gameList;
        $bindings['jsInitialSort'] = $jsInitialSort;

        return view('admin.games.list', $bindings);
    }

    public function add()
    {
        $request = request();

        $genreService = resolve('Services\GenreService');
        /* @var $genreService \App\Services\GenreService */
        $gameGenreService = resolve('Services\GameGenreService');
        /* @var $gameGenreService \App\Services\GameGenreService */

        if ($request->isMethod('post')) {

            $this->validate($request, $this->validationRules);

            $game = $this->serviceGame->create(
                $request->title, $request->link_title, $request->release_date, $request->price_eshop,
                $request->players, $request->upcoming, $request->upcoming_date, $request->overview,
                $request->developer, $request->publisher, $request->amazon_uk_link
            );

            // Update genres
            $gameGenres = [];
            $gameGenreItemList = $request->genre_item;
            if ($gameGenreItemList) {
                foreach ($gameGenreItemList as $genreId => $value) {
                    $gameGenres[] = $genreId;
                }
            }

            // As this is a new game, there are no genres to delete
            //$gameGenreService->deleteGameGenres($gameId);
            $gameId = $game->id;
            if (count($gameGenres) > 0) {
                $gameGenreService->createGameGenreList($gameId, $gameGenres);
            }

            // Done

            // Trigger event
            event(new GameCreated($game));

            return redirect(route('admin.games.list'));

        }

        $bindings = array();

        $bindings['TopTitle'] = 'Admin - Games - Add game';
        $bindings['PanelTitle'] = 'Add game';
        $bindings['FormMode'] = 'add';

        $bindings['GenreList'] = $genreService->getAll();

        return view('admin.games.add', $bindings);
    }

    public function edit($gameId)
    {
        $bindings = array();

        $gameData = $this->serviceGame->find($gameId);
        if (!$gameData) abort(404);

        $request = request();

        $genreService = resolve('Services\GenreService');
        /* @var $genreService \App\Services\GenreService */
        $gameGenreService = resolve('Services\GameGenreService');
        /* @var $gameGenreService \App\Services\GameGenreService */

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';

            $this->validate($request, $this->validationRules);

            $this->serviceGame->edit(
                $gameData,
                $request->title, $request->link_title, $request->release_date, $request->price_eshop,
                $request->players, $request->upcoming, $request->upcoming_date, $request->overview,
                $request->developer, $request->publisher, $request->amazon_uk_link
            );

            // Update genres
            $gameGenres = [];
            $gameGenreItemList = $request->genre_item;
            if ($gameGenreItemList) {
                foreach ($gameGenreItemList as $genreId => $value) {
                    $gameGenres[] = $genreId;
                }
            }

            $gameGenreService->deleteGameGenres($gameId);
            if (count($gameGenres) > 0) {
                $gameGenreService->createGameGenreList($gameId, $gameGenres);
            }

            // Done

            return redirect(route('admin.games.list'));

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['TopTitle'] = 'Admin - Games - Edit game';
        $bindings['PanelTitle'] = 'Edit game';
        $bindings['GameData'] = $gameData;
        $bindings['GameId'] = $gameId;

        $bindings['GenreList'] = $genreService->getAll();
        $bindings['GameGenreList'] = $gameGenreService->getByGame($gameId);

        return view('admin.games.edit', $bindings);
    }
}