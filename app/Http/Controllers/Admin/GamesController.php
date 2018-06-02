<?php

namespace App\Http\Controllers\Admin;

use App\Services\GameReleaseDateService;
use App\Services\GameGenreService;
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
        'developer' => 'max:100',
        'publisher' => 'max:100',
        'media_folder' => 'max:100',
    ];

    public function showList($report = null)
    {
        $serviceGameReleaseDate = resolve('Services\GameReleaseDateService');
        /* @var $serviceGameReleaseDate GameReleaseDateService */
        $serviceGameGenre = resolve('Services\GameGenreService');
        /* @var $serviceGameGenre GameGenreService */

        $bindings = [];

        $bindings['TopTitle'] = 'Admin - Games';
        $bindings['PanelTitle'] = 'Games';

        if ($report == null) {
            $bindings['ActiveNav'] = 'all';
            $gameList = $this->serviceGame->getAll($this->region);
            $jsInitialSort = "[ 0, 'desc']";
        } else {
            $bindings['ActiveNav'] = $report;
            switch ($report) {
                case 'released':
                    $gameList = $serviceGameReleaseDate->getReleased($this->region);
                    $jsInitialSort = "[ 2, 'desc']";
                    break;
                // Data to be filled in
                case 'no-genre':
                    $gameList = $serviceGameGenre->getGamesWithoutGenres($this->region);
                    $jsInitialSort = "[ 0, 'desc']";
                    break;
                case 'no-dev-or-pub':
                    $gameList = $this->serviceGame->getWithoutDevOrPub();
                    $jsInitialSort = "[ 2, 'asc'], [ 1, 'asc']";
                    break;
                case 'no-video-url':
                    $gameList = $this->serviceGame->getWithoutVideoUrl();
                    $jsInitialSort = "[ 0, 'desc']";
                    break;
                case 'no-amazon-uk-link':
                    $gameList = $this->serviceGame->getWithoutAmazonUkLink();
                    $jsInitialSort = "[ 0, 'desc']";
                    break;
                // Upcoming
                case 'upcoming':
                    $gameList = $serviceGameReleaseDate->getUpcoming($this->region);
                    $jsInitialSort = "[ 2, 'asc'], [ 1, 'asc']";
                    break;
                case 'upcoming-2018-with-dates':
                    $gameList = $serviceGameReleaseDate->getUpcomingYearWithDates(2018, $this->region);
                    $jsInitialSort = "[ 2, 'asc'], [ 1, 'asc']";
                    break;
                case 'upcoming-2018-with-quarters':
                    $gameList = $serviceGameReleaseDate->getUpcomingYearQuarters(2018, $this->region);
                    $jsInitialSort = "[ 2, 'asc'], [ 1, 'asc']";
                    break;
                case 'upcoming-2018-sometime':
                    $gameList = $serviceGameReleaseDate->getUpcomingYearXs(2018, $this->region);
                    $jsInitialSort = "[ 2, 'asc'], [ 1, 'asc']";
                    break;
                case 'upcoming-beyond':
                    $gameList = $serviceGameReleaseDate->getUpcomingFuture(2018, $this->region);
                    $jsInitialSort = "[ 2, 'asc'], [ 1, 'asc']";
                    break;
                case 'upcoming-tba':
                    $gameList = $serviceGameReleaseDate->getUpcomingTBA($this->region);
                    $jsInitialSort = "[ 2, 'asc'], [ 1, 'asc']";
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
        $serviceGameReleaseDate = resolve('Services\GameReleaseDateService');
        /* @var $serviceGameReleaseDate GameReleaseDateService */

        if ($request->isMethod('post')) {

            $this->validate($request, $this->validationRules);

            $game = $this->serviceGame->create(
                $request->title, $request->link_title, $request->price_eshop, $request->players,
                $request->developer, $request->publisher, $request->amazon_uk_link, $request->overview,
                $request->media_folder, $request->video_url
            );
            $gameId = $game->id;

            // Update release dates
            $regionsToUpdate = ['eu', 'us', 'jp'];

            foreach ($regionsToUpdate as $region) {

                $releaseDateField = 'release_date_'.$region;
                $isReleasedField = 'is_released_'.$region;
                $upcomingDateField = 'upcoming_date_'.$region;

                $releaseDate = $request->{$releaseDateField};
                $released = $request->{$isReleasedField};
                $upcomingDate = $request->{$upcomingDateField};

                // As this is a new game, we should create all of the regional data
                $serviceGameReleaseDate->createGameReleaseDate($gameId, $region, $releaseDate, $released, $upcomingDate);
            }

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
            if (count($gameGenres) > 0) {
                $gameGenreService->createGameGenreList($gameId, $gameGenres);
            }

            // Done

            // Trigger event
            event(new GameCreated($game));

            return redirect(route('admin.games.list'));

        }

        $bindings = array();

        $bindings['RegionList'] = ['eu' => 'Europe', 'us' => 'US', 'jp' => 'Japan'];

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
        $serviceGameReleaseDate = resolve('Services\GameReleaseDateService');
        /* @var $serviceGameReleaseDate GameReleaseDateService */

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';

            $this->validate($request, $this->validationRules);

            $this->serviceGame->edit(
                $gameData,
                $request->title, $request->link_title, $request->price_eshop, $request->players,
                $request->developer, $request->publisher, $request->amazon_uk_link, $request->overview,
                $request->media_folder, $request->video_url
            );

            // Update release dates
            $regionsToUpdate = ['eu', 'us', 'jp'];

            foreach ($regionsToUpdate as $region) {

                $releaseDateField = 'release_date_'.$region;
                $isReleasedField = 'is_released_'.$region;
                $upcomingDateField = 'upcoming_date_'.$region;

                $releaseDate = $request->{$releaseDateField};
                $released = $request->{$isReleasedField};
                $upcomingDate = $request->{$upcomingDateField};

                // Check if existing data is available before updating!
                $regionData = $serviceGameReleaseDate->getByGameAndRegion($gameId, $region);
                if ($regionData) {
                    // Edit existing
                    $serviceGameReleaseDate->editGameReleaseDate($regionData, $releaseDate, $released, $upcomingDate);
                } else {
                    // Create new
                    $serviceGameReleaseDate->createGameReleaseDate($gameId, $region, $releaseDate, $released, $upcomingDate);
                }

            }

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

        $bindings['RegionList'] = ['eu' => 'Europe', 'us' => 'US', 'jp' => 'Japan'];

        $bindings['TopTitle'] = 'Admin - Games - Edit game';
        $bindings['PanelTitle'] = 'Edit game';
        $bindings['GameData'] = $gameData;
        $bindings['GameId'] = $gameId;

        // Load game release date info for the form
        $gameReleaseDatesDb = $serviceGameReleaseDate->getByGame($gameId);

        $gameReleaseDates = [];
        $dateFormData = [];
        foreach ($gameReleaseDatesDb as $gameReleaseDate) {
            $region = $gameReleaseDate->region;
            $releaseDate = $gameReleaseDate->release_date;
            $upcomingDate = $gameReleaseDate->upcoming_date;
            $isReleased = $gameReleaseDate->is_released;
            $dateFormData['release_date'] = $releaseDate;
            $dateFormData['upcoming_date'] = $upcomingDate;
            $dateFormData['is_released'] = $isReleased;
            $gameReleaseDates[$region] = $dateFormData;
        }

        $bindings['GameReleaseDates'] = $gameReleaseDates;

        $bindings['GenreList'] = $genreService->getAll();
        $bindings['GameGenreList'] = $gameGenreService->getByGame($gameId);

        return view('admin.games.edit', $bindings);
    }
}