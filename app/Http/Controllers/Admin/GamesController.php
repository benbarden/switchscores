<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Services\ServiceContainer;

use App\Events\GameCreated;

use Auth;

class GamesController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

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
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $regionCode = \Request::get('regionCode');
        $regionOverride = \Request::get('regionOverride');

        $serviceGame = $serviceContainer->getGameService();
        $serviceGameReleaseDate = $serviceContainer->getGameReleaseDateService();
        $serviceGameGenre = $serviceContainer->getGameGenreService();
        $serviceGameTag = $serviceContainer->getGameTagService();

        $bindings = [];

        $bindings['TopTitle'] = 'Admin - Games';
        $bindings['PanelTitle'] = 'Games';

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
                    }
                    $gameList = $serviceGame->getActionListGamesForRelease($regionCode);
                    $jsInitialSort = "[ 3, 'asc'], [ 1, 'asc']";
                    break;
                case 'action-list-recent-no-nintendo-url':
                    $gameList = $serviceGame->getActionListRecentNoNintendoUrl($regionCode);
                    $jsInitialSort = "[ 3, 'asc'], [ 1, 'asc']";
                    break;
                case 'action-list-upcoming-no-nintendo-url':
                    $gameList = $serviceGame->getActionListUpcomingNoNintendoUrl($regionCode);
                    $jsInitialSort = "[ 3, 'asc'], [ 1, 'asc']";
                    break;
                case 'action-list-nintendo-url-no-packshots':
                    $gameList = $serviceGame->getActionListNintendoUrlNoPackshots($regionCode);
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
                // Missing data
                case 'no-dev-or-pub':
                    $gameList = $serviceGame->getWithoutDevOrPub();
                    $jsInitialSort = "[ 3, 'asc'], [ 1, 'asc']";
                    break;
                case 'no-tags':
                    $gameList = $serviceGameTag->getGamesWithoutTags($regionCode);
                    $jsInitialSort = "[ 0, 'desc']";
                    break;
                case 'no-genre':
                    $gameList = $serviceGameGenre->getGamesWithoutGenres($regionCode);
                    $jsInitialSort = "[ 0, 'desc']";
                    break;
                case 'no-eshop-europe-link':
                    $gameList = $serviceGame->getByNullField('eshop_europe_fs_id', $regionCode);
                    $jsInitialSort = "[ 3, 'asc'], [ 0, 'asc']";
                    break;
                case 'no-nintendo-page-url':
                    $gameList = $serviceGame->getByNullField('nintendo_page_url', $regionCode);
                    $jsInitialSort = "[ 3, 'asc'], [ 0, 'asc']";
                    break;
                case 'no-vendor-page-url':
                    $gameList = $serviceGame->getByNullField('vendor_page_url', $regionCode);
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
                case 'no-twitter-id':
                    $gameList = $serviceGame->getByNullField('twitter_id', $regionCode);
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
            $game = $serviceGame->create(
                $request->title, $request->link_title, $request->price_eshop, $request->players,
                $request->developer, $request->publisher, $request->amazon_uk_link, $request->overview,
                $request->media_folder, $request->video_url,
                $request->boxart_url, $request->boxart_square_url,
                $request->vendor_page_url, $request->nintendo_page_url,
                $request->twitter_id, $request->eshop_europe_fs_id
            );
            $gameId = $game->id;

            // Add title hash
            $gameTitleHash = $serviceGameTitleHash->create($request->title, $titleHash, $gameId);

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
                $serviceGameGenre->createGameGenreList($gameId, $gameGenres);
            }

            // Done

            // Trigger event
            event(new GameCreated($game));

            return redirect(route('admin.games.list'));

        }

        $bindings = [];

        $bindings['RegionList'] = ['eu' => 'Europe', 'us' => 'US', 'jp' => 'Japan'];

        $bindings['TopTitle'] = 'Admin - Games - Add game';
        $bindings['PanelTitle'] = 'Add game';
        $bindings['FormMode'] = 'add';

        $bindings['GenreList'] = $serviceGenre->getAll();
        $bindings['EshopEuropeList'] = $serviceEshopEurope->getAll();

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

            $serviceGame->edit(
                $gameData,
                $request->title, $request->link_title, $request->price_eshop, $request->players,
                $request->developer, $request->publisher, $request->amazon_uk_link, $request->overview,
                $request->media_folder, $request->video_url,
                $request->boxart_url, $request->boxart_square_url,
                $request->vendor_page_url, $request->nintendo_page_url,
                $request->twitter_id, $request->eshop_europe_fs_id
            );

            // Update release dates

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

            $serviceGameGenre->deleteGameGenres($gameId);
            if (count($gameGenres) > 0) {
                $serviceGameGenre->createGameGenreList($gameId, $gameGenres);
            }

            // Done
            if ($request->button_pressed == 'save-return-to-list') {
                return redirect('/admin/games/list/'.$gameListFilter);
            } elseif ($gameListFilter && $nextId) {
                return redirect('/admin/games/edit/'.$nextId.'?filter='.$gameListFilter);
            } else {
                return redirect(route('admin.games.list'));
            }

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
        $wikiFormats = [];

        foreach ($regionsToUpdate as $region) {
            $wikiFormats[$region] = null;
        }

        foreach ($gameReleaseDatesDb as $gameReleaseDate) {
            $region = $gameReleaseDate->region;
            $releaseDate = $gameReleaseDate->release_date;
            $upcomingDate = $gameReleaseDate->upcoming_date;
            $isReleased = $gameReleaseDate->is_released;
            $dateFormData['release_date'] = $releaseDate;
            $dateFormData['upcoming_date'] = $upcomingDate;
            $dateFormData['is_released'] = $isReleased;
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
        $serviceChartsRankingGlobal = $serviceContainer->getChartsRankingGlobalService();

        // Deletion
        $serviceActivityFeed = $serviceContainer->getActivityFeedService();
        $serviceFeedItemGames = $serviceContainer->getFeedItemGameService();
        $serviceGameReleaseDates = $serviceContainer->getGameReleaseDateService();
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

        $gameChartsRankingGlobalEu = $serviceChartsRankingGlobal->getByGameEu($gameId);
        $gameChartsRankingGlobalUs = $serviceChartsRankingGlobal->getByGameUs($gameId);
        $totalChartsCount = count($gameChartsRankingGlobalEu) + count($gameChartsRankingGlobalUs);
        if ($totalChartsCount > 0) {
            $customErrors[] = 'Game is linked to '.count($gameReviews).' chart(s)';
        }

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'delete-post';

            $serviceActivityFeed->deleteByGameId($gameId);
            $serviceFeedItemGames->deleteByGameId($gameId);
            $serviceGameReleaseDates->deleteByGameId($gameId);
            DB::table('game_images')->where('game_id', $gameId)->delete();
            $serviceGameGenres->deleteGameGenres($gameId);
            $serviceGameTitleHashes->deleteByGameId($gameId);
            $serviceGameTags->deleteGameTags($gameId);
            $serviceGame->deleteGame($gameId);

            // Done

            return redirect(route('admin.games.list'));

        } else {

            $bindings['FormMode'] = 'delete';

        }

        $bindings['TopTitle'] = 'Admin - Games - Delete game';
        $bindings['PanelTitle'] = 'Delete game';
        $bindings['GameData'] = $gameData;
        $bindings['GameId'] = $gameId;
        $bindings['ErrorsCustom'] = $customErrors;

        return view('admin.games.delete', $bindings);
    }

    public function releaseGame()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */
        $gameService = $serviceContainer->getGameService();
        $userService = $serviceContainer->getUserService();
        $gameReleaseDateService = $serviceContainer->getGameReleaseDateService();

        $userId = Auth::id();

        $user = $userService->find($userId);
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

        $gameReleaseDate = $gameReleaseDateService->getByGameAndRegion($gameId, $regionCode);
        if (!$gameReleaseDate) {
            return response()->json(['error' => 'gameReleaseDate not found for game: '.$gameId.'; region: '.$regionCode], 400);
        }

        $gameReleaseDateService->markAsReleased($gameReleaseDate);

        $data = array(
            'status' => 'OK'
        );
        return response()->json($data, 200);
    }
}