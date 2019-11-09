<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Traits\SiteRequestData;
use App\Traits\WosServices;

use App\Services\ServiceContainer;
use App\Events\GameCreated;

use App\Construction\Game\GameBuilder;
use App\Construction\Game\GameDirector;

use App\Construction\GameChangeHistory\Director as GameChangeHistoryDirector;
use App\Construction\GameChangeHistory\Builder as GameChangeHistoryBuilder;

use App\Construction\GameReleaseDate\Director as GameReleaseDateDirector;
use App\Construction\GameReleaseDate\Builder as GameReleaseDateBuilder;

use App\Factories\GameDirectorFactory;
use App\Factories\GameChangeHistoryFactory;
use App\Factories\EshopEuropeUpdateGameFactory;
use App\Factories\EshopEuropeRedownloadPackshotsFactory;

use Auth;

class GamesController extends Controller
{
    use WosServices, SiteRequestData;
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRules = [
        'title' => 'required|max:255',
        'link_title' => 'required|max:100',
        'price_eshop' => 'max:6',
        'players' => 'max:10',
        'media_folder' => 'max:100',
    ];

    public function showList($report = null)
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

    public function add()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $request = request();

        $serviceGame = $serviceContainer->getGameService();
        $serviceGenre = $serviceContainer->getGenreService();
        $serviceGameGenre = $serviceContainer->getGameGenreService();
        $serviceGameReleaseDate = $serviceContainer->getGameReleaseDateService();
        $serviceGameTitleHash = $serviceContainer->getGameTitleHashService();
        $serviceEshopEurope = $serviceContainer->getEshopEuropeGameService();
        $servicePrimaryTypes = $serviceContainer->getGamePrimaryTypeService();
        $serviceGameSeries = $serviceContainer->getGameSeriesService();

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

            // Update release dates
            $gameReleaseDateDirector = new GameReleaseDateDirector();

            $regionsToUpdate = $gameReleaseDateDirector->getRegionList();

            foreach ($regionsToUpdate as $region) {

                $gameReleaseDateBuilder = new GameReleaseDateBuilder();
                $gameReleaseDateDirector->setBuilder($gameReleaseDateBuilder);
                $gameReleaseDateDirector->buildNewReleaseDate($region, $gameId, $request->post());
                $gameReleaseDate = $gameReleaseDateBuilder->getGameReleaseDate();
                $gameReleaseDate->save();

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
                $serviceGameGenre->createGameGenreList($gameId, $gameGenres);
            }

            // Game change history
            $gameChangeHistoryDirector = new GameChangeHistoryDirector();
            $gameChangeHistoryBuilder = new GameChangeHistoryBuilder();

            $gameChangeHistoryBuilder->setGame($game);
            $gameChangeHistoryDirector->setBuilder($gameChangeHistoryBuilder);
            $gameChangeHistoryDirector->setTableNameGames();
            $gameChangeHistoryDirector->buildAdminInsert();
            $gameChangeHistoryDirector->setUserId(Auth::user()->id);
            $gameChangeHistory = $gameChangeHistoryBuilder->getGameChangeHistory();
            $gameChangeHistory->save();

            // Done

            // Trigger event
            event(new GameCreated($game));

            //return redirect('/admin/games/list?lastaction=add&lastgameid='.$gameId);
            return redirect('/admin/games/detail/'.$gameId.'?lastaction=add&lastgameid='.$gameId);

        }

        $bindings = [];

        $bindings['RegionList'] = ['eu' => 'Europe', 'us' => 'US', 'jp' => 'Japan'];

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
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $request = request();

        $gameListFilter = $request->filter;

        $bindings = [];

        $serviceGame = $serviceContainer->getGameService();
        $serviceGenre = $serviceContainer->getGenreService();
        $serviceGameGenre = $serviceContainer->getGameGenreService();
        $serviceGameReleaseDate = $serviceContainer->getGameReleaseDateService();
        $serviceEshopEurope = $serviceContainer->getEshopEuropeGameService();
        $servicePrimaryTypes = $serviceContainer->getGamePrimaryTypeService();
        $serviceGameSeries = $serviceContainer->getGameSeriesService();

        $gameData = $serviceGame->find($gameId);
        if (!$gameData) abort(404);

        $gameOrig = $gameData->fresh();

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

            // Update release dates
            $gameReleaseDateDirector = new GameReleaseDateDirector();

            $regionsToUpdate = $gameReleaseDateDirector->getRegionList();

            foreach ($regionsToUpdate as $region) {

                $gameReleaseDateBuilder = new GameReleaseDateBuilder();
                $gameReleaseDateDirector->setBuilder($gameReleaseDateBuilder);

                // Check if existing data is available before updating
                $gameReleaseDateExisting = $serviceGameReleaseDate->getByGameAndRegion($gameId, $region);
                if ($gameReleaseDateExisting) {
                    $gameReleaseDateDirector->buildExistingReleaseDate($region, $gameReleaseDateExisting, $request->post());
                } else {
                    $gameReleaseDateDirector->buildNewReleaseDate($region, $gameId, $request->post());
                }

                $gameReleaseDate = $gameReleaseDateBuilder->getGameReleaseDate();
                $gameReleaseDate->save();

            }

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

            // Game change history
            $gameData->refresh();

            GameChangeHistoryFactory::makeHistory($gameData, $gameOrig, Auth::user()->id, 'games');

            // Done
            if ($request->button_pressed == 'save-return-to-list') {
                //return redirect('/admin/games/list/'.$gameListFilter.'?lastaction=edit&lastgameid='.$gameId);
                return redirect('/admin/games/detail/'.$gameId.'?lastaction=edit&lastgameid='.$gameId);
            } elseif ($gameListFilter && $nextId) {
                return redirect('/admin/games/edit/'.$nextId.'?filter='.$gameListFilter);
            } else {
                return redirect('/admin/games/list?lastaction=edit&lastgameid='.$gameId);
            }

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['RegionList'] = ['eu' => 'Europe', 'us' => 'US', 'jp' => 'Japan'];

        $bindings['TopTitle'] = 'Admin - Games - Edit game';
        $bindings['PageTitle'] = 'Edit game';
        $bindings['GameData'] = $gameData;
        $bindings['GameId'] = $gameId;

        // Load game release date info for the form
        $gameReleaseDatesDb = $serviceGameReleaseDate->getByGame($gameId);

        $gameReleaseDates = [];
        $dateFormData = [];
        $wikiFormats = [];

        foreach ($regionsToUpdate as $region) {
            $wikiFormats[$region] = null;
        }

        foreach ($gameReleaseDatesDb as $gameReleaseDate) {
            $region = $gameReleaseDate->region;
            $releaseDate = $gameReleaseDate->release_date;
            $upcomingDate = $gameReleaseDate->upcoming_date;
            $isReleased = $gameReleaseDate->is_released;
            $isLocked = $gameReleaseDate->is_locked;
            $dateFormData['release_date'] = $releaseDate;
            $dateFormData['upcoming_date'] = $upcomingDate;
            $dateFormData['is_released'] = $isReleased;
            $dateFormData['is_locked'] = $isLocked;
            $gameReleaseDates[$region] = $dateFormData;

            if ($releaseDate) {
                $dtRelDate = new \DateTime($releaseDate);
                $dtRelDateY = $dtRelDate->format('Y');
                $dtRelDateM = $dtRelDate->format('m');
                $dtRelDateD = $dtRelDate->format('d');
                $wikiFormats[$region] = sprintf('{{dts|%s|%s|%s}}', $dtRelDateY, $dtRelDateM, $dtRelDateD);
            }

        }

        foreach ($regionsToUpdate as $region) {
            if ($wikiFormats[$region] == null) {
                $wikiFormats[$region] = 'Unreleased';
            }
        }
        $bindings['WikiDateList'] = sprintf('%s||%s||%s', $wikiFormats['jp'], $wikiFormats['us'], $wikiFormats['eu']);

        $bindings['GameReleaseDates'] = $gameReleaseDates;

        $bindings['GenreList'] = $serviceGenre->getAll();
        $bindings['GameGenreList'] = $serviceGameGenre->getByGame($gameId);
        $bindings['EshopEuropeList'] = $serviceEshopEurope->getAll();
        $bindings['GameSeriesList'] = $serviceGameSeries->getAll();
        $bindings['PrimaryTypeList'] = $servicePrimaryTypes->getAll();

        return view('admin.games.edit', $bindings);
    }

    public function delete($gameId)
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        // Core
        $serviceGame = $serviceContainer->getGameService();

        // Validation
        $serviceNews = $serviceContainer->getNewsService();
        $serviceUserListItem = $serviceContainer->getUserListItemService();
        $serviceReviewLink = $serviceContainer->getReviewLinkService();

        // Deletion
        $serviceFeedItemGames = $serviceContainer->getFeedItemGameService();
        $serviceGameReleaseDates = $serviceContainer->getGameReleaseDateService();
        $serviceGameChangeHistory = $serviceContainer->getGameChangeHistoryService();
        $serviceGameDeveloper = $serviceContainer->getGameDeveloperService();
        $serviceGamePublisher = $serviceContainer->getGamePublisherService();

        // No service for game_images
        $serviceGameGenres = $serviceContainer->getGameGenreService();
        $serviceGameTitleHashes = $serviceContainer->getGameTitleHashService();
        $serviceGameTags = $serviceContainer->getGameTagService();

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

        $gameUserListItem = $serviceUserListItem->getByGame($gameId);
        if (count($gameUserListItem) > 0) {
            $customErrors[] = 'Game is linked to '.count($gameUserListItem).' user list(s)';
        }

        $gameReviews = $serviceReviewLink->getByGame($gameId);
        if (count($gameReviews) > 0) {
            $customErrors[] = 'Game is linked to '.count($gameReviews).' review(s)';
        }

        $gameChartsRankingGlobalEu = $this->getServiceChartsRankingGlobal()->getByGameEu($gameId);
        $gameChartsRankingGlobalUs = $this->getServiceChartsRankingGlobal()->getByGameUs($gameId);
        $totalChartsCount = count($gameChartsRankingGlobalEu) + count($gameChartsRankingGlobalUs);
        if ($totalChartsCount > 0) {
            $customErrors[] = 'Game is linked to '.count($gameReviews).' chart(s)';
        }

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'delete-post';

            $this->getServiceActivityFeed()->deleteByGameId($gameId);
            $serviceFeedItemGames->deleteByGameId($gameId);
            $serviceGameReleaseDates->deleteByGameId($gameId);
            DB::table('game_images')->where('game_id', $gameId)->delete();
            $serviceGameGenres->deleteGameGenres($gameId);
            $serviceGameTitleHashes->deleteByGameId($gameId);
            $serviceGameTags->deleteGameTags($gameId);
            $serviceGameChangeHistory->deleteByGameId($gameId);
            $serviceGameDeveloper->deleteByGameId($gameId);
            $serviceGamePublisher->deleteByGameId($gameId);
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
        $serviceGameReleaseDate = $this->getServiceGameReleaseDate();

        $userId = Auth::id();

        $user = $serviceUser->find($userId);
        if (!$user) {
            return response()->json(['error' => 'Cannot find user!'], 400);
        }

        $request = request();

        $gameId = $request->gameId;
        $regionCode = $request->regionCode;
        if (!$gameId) {
            return response()->json(['error' => 'Missing data: gameId'], 400);
        }
        if (!$regionCode) {
            return response()->json(['error' => 'Missing data: regionCode'], 400);
        }

        $gameData = $serviceGame->find($gameId);
        if (!$gameId) {
            return response()->json(['error' => 'Game not found: '.$gameId], 400);
        }

        $gameReleaseDate = $serviceGameReleaseDate->getByGameAndRegion($gameId, $regionCode);
        if (!$gameReleaseDate) {
            return response()->json(['error' => 'gameReleaseDate not found for game: '.$gameId.'; region: '.$regionCode], 400);
        }

        $serviceGameReleaseDate->markAsReleased($gameReleaseDate);

        if ($regionCode == 'eu') {
            $gameOrig = $gameData;

            $dateNow = new \DateTime('now');
            $gameData->eu_released_on = $dateNow->format('Y-m-d H:i:s');
            $gameData->save();

            // Game change history
            $gameData->refresh();
            GameChangeHistoryFactory::makeHistory($gameData, $gameOrig, Auth::user()->id, 'games');
        }

        $data = array(
            'status' => 'OK'
        );
        return response()->json($data, 200);
    }

    public function updateEshopData()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */
        $serviceGame = $serviceContainer->getGameService();
        $serviceUser = $serviceContainer->getUserService();

        $userId = Auth::id();

        $user = $serviceUser->find($userId);
        if (!$user) {
            return response()->json(['error' => 'Cannot find user!'], 400);
        }

        $request = request();

        $gameId = $request->gameId;
        $regionCode = $request->regionCode;
        if (!$gameId) {
            return response()->json(['error' => 'Missing data: gameId'], 400);
        }
        if (!$regionCode) {
            return response()->json(['error' => 'Missing data: regionCode'], 400);
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
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */
        $serviceGame = $serviceContainer->getGameService();
        $serviceUser = $serviceContainer->getUserService();

        $userId = Auth::id();

        $user = $serviceUser->find($userId);
        if (!$user) {
            return response()->json(['error' => 'Cannot find user!'], 400);
        }

        $request = request();

        $gameId = $request->gameId;
        $regionCode = $request->regionCode;
        if (!$gameId) {
            return response()->json(['error' => 'Missing data: gameId'], 400);
        }
        if (!$regionCode) {
            return response()->json(['error' => 'Missing data: regionCode'], 400);
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