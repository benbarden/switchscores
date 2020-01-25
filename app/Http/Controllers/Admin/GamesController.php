<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Events\GameCreated;

use App\Construction\Game\GameBuilder;
use App\Construction\Game\GameDirector;

use App\Factories\GameDirectorFactory;
use App\Factories\EshopEuropeUpdateGameFactory;
use App\Factories\EshopEuropeRedownloadPackshotsFactory;

use App\Traits\SwitchServices;
use App\Traits\AuthUser;

class GamesController extends Controller
{
    use SwitchServices;
    use AuthUser;

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRules = [
        'title' => 'required|max:255',
        'link_title' => 'required|max:100',
        'price_eshop' => 'max:6',
        'players' => 'max:10',
    ];

    public function showList($report = null)
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

    public function add()
    {
        $serviceGenre = $this->getServiceGenre();
        $serviceGameGenre = $this->getServiceGameGenre();
        $serviceGameTitleHash = $this->getServiceGameTitleHash();
        $serviceEshopEurope = $this->getServiceEshopEuropeGame();
        $servicePrimaryTypes = $this->getServiceGamePrimaryType();
        $serviceGameSeries = $this->getServiceGameSeries();

        $request = request();

        if ($request->isMethod('post')) {

            //$this->validate($request, $this->validationRules);

            $validator = Validator::make($request->all(), $this->validationRules);

            if ($validator->fails()) {
                return redirect(route('admin.games.add'))
                    ->withErrors($validator)
                    ->withInput();
            }

            // Check title hash is unique
            $titleHash = $serviceGameTitleHash->generateHash($request->title);
            $existingTitleHash = $serviceGameTitleHash->getByHash($titleHash);

            $validator->after(function ($validator) use ($existingTitleHash) {
                // Check for duplicates
                if ($existingTitleHash != null) {
                    $validator->errors()->add('title', 'Title already exists for another record!');
                }
            });

            if ($validator->fails()) {
                return redirect(route('admin.games.add'))
                    ->withErrors($validator)
                    ->withInput();
            }

            // Add game
            $gameDirector = new GameDirector();
            $gameBuilder = new GameBuilder();
            $gameDirector->setBuilder($gameBuilder);
            $gameDirector->buildNewGame($request->post());
            $game = $gameBuilder->getGame();
            $game->save();
            $gameId = $game->id;

            // Add title hash
            $gameTitleHash = $serviceGameTitleHash->create($request->title, $titleHash, $gameId);

            // Check eu_released_on
            if ($request->eu_is_released == 1) {
                $dateNow = new \DateTime('now');
                $game->eu_released_on = $dateNow->format('Y-m-d H:i:s');
                $game->save();
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
            if (count($gameGenres) > 0) {
                $serviceGameGenre->createGameGenreList($gameId, $gameGenres);
            }

            // Done

            // Trigger event
            event(new GameCreated($game));

            //return redirect('/admin/games/list?lastaction=add&lastgameid='.$gameId);
            return redirect('/staff/games/detail/'.$gameId.'?lastaction=add&lastgameid='.$gameId);

        }

        $bindings = [];

        $bindings['TopTitle'] = 'Admin - Games - Add game';
        $bindings['PageTitle'] = 'Add game';
        $bindings['FormMode'] = 'add';

        $bindings['GenreList'] = $serviceGenre->getAll();
        $bindings['EshopEuropeList'] = $serviceEshopEurope->getAll();
        $bindings['GameSeriesList'] = $serviceGameSeries->getAll();
        $bindings['PrimaryTypeList'] = $servicePrimaryTypes->getAll();

        return view('admin.games.add', $bindings);
    }

    public function edit($gameId)
    {
        $request = request();

        $serviceGame = $this->getServiceGame();
        $serviceGenre = $this->getServiceGenre();
        $serviceGameGenre = $this->getServiceGameGenre();
        $serviceEshopEurope = $this->getServiceEshopEuropeGame();
        $servicePrimaryTypes = $this->getServiceGamePrimaryType();
        $serviceGameSeries = $this->getServiceGameSeries();

        $gameListFilter = $request->filter;

        $bindings = [];

        $gameData = $serviceGame->find($gameId);
        if (!$gameData) abort(404);

        // Filters and next game id
        $bindings['GameListFilter'] = $gameListFilter;
        $nextId = $serviceGame->getNextId($gameListFilter, $gameId);
        if ($nextId) {
            $bindings['GameListFilterNextId'] = $nextId;
        }

        $regionsToUpdate = ['eu', 'us', 'jp'];

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';

            $this->validate($request, $this->validationRules);

            GameDirectorFactory::updateExisting($gameData, $request->post());

            // Update genres
            $gameGenres = [];
            $gameGenreItemList = $request->genre_item;
            if ($gameGenreItemList) {
                foreach ($gameGenreItemList as $genreId => $value) {
                    $gameGenres[] = $genreId;
                }
            }

            $serviceGameGenre->deleteGameGenres($gameId);
            if (count($gameGenres) > 0) {
                $serviceGameGenre->createGameGenreList($gameId, $gameGenres);
            }

            // Done
            if ($request->button_pressed == 'save-return-to-list') {
                //return redirect('/admin/games/list/'.$gameListFilter.'?lastaction=edit&lastgameid='.$gameId);
                return redirect('/staff/games/detail/'.$gameId.'?lastaction=edit&lastgameid='.$gameId);
            } elseif ($gameListFilter && $nextId) {
                return redirect('/admin/games/edit/'.$nextId.'?filter='.$gameListFilter);
            } else {
                return redirect('/admin/games/list?lastaction=edit&lastgameid='.$gameId);
            }

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['TopTitle'] = 'Admin - Games - Edit game';
        $bindings['PageTitle'] = 'Edit game';
        $bindings['GameData'] = $gameData;
        $bindings['GameId'] = $gameId;

        $bindings['GenreList'] = $serviceGenre->getAll();
        $bindings['GameGenreList'] = $serviceGameGenre->getByGame($gameId);
        $bindings['EshopEuropeList'] = $serviceEshopEurope->getAll();
        $bindings['GameSeriesList'] = $serviceGameSeries->getAll();
        $bindings['PrimaryTypeList'] = $servicePrimaryTypes->getAll();

        return view('admin.games.edit', $bindings);
    }

    public function delete($gameId)
    {
        // Core
        $serviceGame = $this->getServiceGame();
        $serviceGameTitleHash = $this->getServiceGameTitleHash();

        // Categorisation
        $serviceGameGenre = $this->getServiceGameGenre();
        $serviceGameTag = $this->getServiceGameTag();

        // Validation
        $serviceNews = $this->getServiceNews();
        $serviceReviewLink = $this->getServiceReviewLink();

        // Deletion
        $serviceFeedItemGame = $this->getServiceFeedItemGame();
        $serviceGameDeveloper = $this->getServiceGameDeveloper();
        $serviceGamePublisher = $this->getServiceGamePublisher();

        $gameData = $serviceGame->find($gameId);
        if (!$gameData) abort(404);

        $bindings = [];
        $customErrors = [];

        $request = request();

        // Validation: check for any reason we should not allow the game to be deleted.
        $gameNews = $serviceNews->getByGameId($gameId);
        if (count($gameNews) > 0) {
            $customErrors[] = 'Game is linked to '.count($gameNews).' news article(s)';
        }

        $gameReviews = $serviceReviewLink->getByGame($gameId);
        if (count($gameReviews) > 0) {
            $customErrors[] = 'Game is linked to '.count($gameReviews).' review(s)';
        }

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'delete-post';

            $this->getServiceActivityFeed()->deleteByGameId($gameId);
            $serviceFeedItemGame->deleteByGameId($gameId);
            $serviceGameGenre->deleteGameGenres($gameId);
            $serviceGameTitleHash->deleteByGameId($gameId);
            $serviceGameTag->deleteGameTags($gameId);
            $serviceGameDeveloper->deleteByGameId($gameId);
            $serviceGamePublisher->deleteByGameId($gameId);
            $this->getServiceGameImportRuleEshop()->deleteByGameId($gameId);
            $this->getServiceGameImportRuleWikipedia()->deleteByGameId($gameId);
            $serviceGame->deleteGame($gameId);

            // Done

            return redirect(route('admin.games.list'));

        } else {

            $bindings['FormMode'] = 'delete';

        }

        $bindings['TopTitle'] = 'Admin - Games - Delete game';
        $bindings['PageTitle'] = 'Delete game';
        $bindings['GameData'] = $gameData;
        $bindings['GameId'] = $gameId;
        $bindings['ErrorsCustom'] = $customErrors;

        return view('admin.games.delete', $bindings);
    }

    public function releaseGame()
    {
        $serviceUser = $this->getServiceUser();
        $serviceGame = $this->getServiceGame();

        $userId = $this->getAuthId();

        $user = $serviceUser->find($userId);
        if (!$user) {
            return response()->json(['error' => 'Cannot find user!'], 400);
        }

        $request = request();

        $gameId = $request->gameId;
        if (!$gameId) {
            return response()->json(['error' => 'Missing data: gameId'], 400);
        }

        $game = $serviceGame->find($gameId);
        if (!$gameId) {
            return response()->json(['error' => 'Game not found: '.$gameId], 400);
        }

        $serviceGame->markAsReleased($game);

        $data = array(
            'status' => 'OK'
        );
        return response()->json($data, 200);
    }

    public function updateEshopData()
    {
        $serviceUser = $this->getServiceUser();
        $serviceGame = $this->getServiceGame();

        $userId = $this->getAuthId();

        $user = $serviceUser->find($userId);
        if (!$user) {
            return response()->json(['error' => 'Cannot find user!'], 400);
        }

        $request = request();

        $gameId = $request->gameId;
        if (!$gameId) {
            return response()->json(['error' => 'Missing data: gameId'], 400);
        }

        $game = $serviceGame->find($gameId);
        if (!$game) {
            return response()->json(['error' => 'Cannot find game!'], 400);
        }

        try {
            EshopEuropeUpdateGameFactory::updateGame($game);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Exception: '.$e->getMessage()], 400);
        }

        $data = array(
            'status' => 'OK'
        );
        return response()->json($data, 200);
    }

    public function redownloadPackshots()
    {
        $serviceUser = $this->getServiceUser();
        $serviceGame = $this->getServiceGame();

        $userId = $this->getAuthId();

        $user = $serviceUser->find($userId);
        if (!$user) {
            return response()->json(['error' => 'Cannot find user!'], 400);
        }

        $request = request();

        $gameId = $request->gameId;
        if (!$gameId) {
            return response()->json(['error' => 'Missing data: gameId'], 400);
        }

        $game = $serviceGame->find($gameId);
        if (!$game) {
            return response()->json(['error' => 'Cannot find game!'], 400);
        }

        try {
            EshopEuropeRedownloadPackshotsFactory::redownloadPackshots($game);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Exception: '.$e->getMessage()], 400);
        }

        $data = array(
            'status' => 'OK'
        );
        return response()->json($data, 200);
    }
}