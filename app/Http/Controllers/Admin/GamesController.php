<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Services\ServiceContainer;

use App\Events\GameCreated;

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

        $serviceGame = $serviceContainer->getGameService();
        $serviceGameReleaseDate = $serviceContainer->getGameReleaseDateService();
        $serviceGameGenre = $serviceContainer->getGameGenreService();

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
                    $jsInitialSort = "[ 2, 'desc']";
                    break;
                // Data to be filled in
                case 'no-genre':
                    $gameList = $serviceGameGenre->getGamesWithoutGenres($regionCode);
                    $jsInitialSort = "[ 0, 'desc']";
                    break;
                case 'no-dev-or-pub':
                    $gameList = $serviceGame->getWithoutDevOrPub();
                    $jsInitialSort = "[ 2, 'asc'], [ 1, 'asc']";
                    break;
                case 'no-boxart':
                    $gameList = $serviceGame->getWithoutBoxart();
                    $jsInitialSort = "[ 0, 'desc']";
                    break;
                case 'no-video-url':
                    $gameList = $serviceGame->getByNullField('video_url');
                    $jsInitialSort = "[ 0, 'desc']";
                    break;
                case 'no-vendor-page-url':
                    $gameList = $serviceGame->getByNullField('vendor_page_url');
                    $jsInitialSort = "[ 0, 'desc']";
                    break;
                case 'no-nintendo-page-url':
                    $gameList = $serviceGame->getByNullField('nintendo_page_url');
                    $jsInitialSort = "[ 0, 'desc']";
                    break;
                case 'no-twitter-id':
                    $gameList = $serviceGame->getByNullField('twitter_id');
                    $jsInitialSort = "[ 0, 'desc']";
                    break;
                case 'no-amazon-uk-link':
                    $gameList = $serviceGame->getWithoutAmazonUkLink();
                    $jsInitialSort = "[ 0, 'desc']";
                    break;
                // Upcoming
                case 'upcoming':
                    $gameList = $serviceGameReleaseDate->getUpcoming($regionCode);
                    $jsInitialSort = "[ 2, 'asc'], [ 1, 'asc']";
                    break;
                case 'upcoming-2018-with-dates':
                    $gameList = $serviceGameReleaseDate->getUpcomingYearWithDates(2018, $regionCode);
                    $jsInitialSort = "[ 2, 'asc'], [ 1, 'asc']";
                    break;
                case 'upcoming-2018-with-quarters':
                    $gameList = $serviceGameReleaseDate->getUpcomingYearQuarters(2018, $regionCode);
                    $jsInitialSort = "[ 2, 'asc'], [ 1, 'asc']";
                    break;
                case 'upcoming-2018-sometime':
                    $gameList = $serviceGameReleaseDate->getUpcomingYearXs(2018, $regionCode);
                    $jsInitialSort = "[ 2, 'asc'], [ 1, 'asc']";
                    break;
                case 'upcoming-beyond':
                    $gameList = $serviceGameReleaseDate->getUpcomingFuture(2018, $regionCode);
                    $jsInitialSort = "[ 2, 'asc'], [ 1, 'asc']";
                    break;
                case 'upcoming-tba':
                    $gameList = $serviceGameReleaseDate->getUpcomingTBA($regionCode);
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
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $request = request();

        $serviceGame = $serviceContainer->getGameService();
        $serviceGenre = $serviceContainer->getGenreService();
        $serviceGameGenre = $serviceContainer->getGameGenreService();
        $serviceGameReleaseDate = $serviceContainer->getGameReleaseDateService();

        if ($request->isMethod('post')) {

            $this->validate($request, $this->validationRules);

            $game = $serviceGame->create(
                $request->title, $request->link_title, $request->price_eshop, $request->players,
                $request->developer, $request->publisher, $request->amazon_uk_link, $request->overview,
                $request->media_folder, $request->video_url,
                $request->boxart_url, $request->boxart_square_url,
                $request->vendor_page_url, $request->nintendo_page_url,
                $request->twitter_id
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

        return view('admin.games.add', $bindings);
    }

    public function edit($gameId)
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $request = request();

        $bindings = [];

        $serviceGame = $serviceContainer->getGameService();
        $serviceGenre = $serviceContainer->getGenreService();
        $serviceGameGenre = $serviceContainer->getGameGenreService();
        $serviceGameReleaseDate = $serviceContainer->getGameReleaseDateService();

        $gameData = $serviceGame->find($gameId);
        if (!$gameData) abort(404);

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
                $request->twitter_id
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

            $serviceGameGenre->deleteGameGenres($gameId);
            if (count($gameGenres) > 0) {
                $serviceGameGenre->createGameGenreList($gameId, $gameGenres);
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

        $bindings['GenreList'] = $serviceGenre->getAll();
        $bindings['GameGenreList'] = $serviceGameGenre->getByGame($gameId);

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
}