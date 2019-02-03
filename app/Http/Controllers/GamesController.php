<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use Illuminate\Support\Collection;

use App\Services\ServiceContainer;

use Auth;

class GamesController extends Controller
{
    //use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function landing()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $regionCode = \Request::get('regionCode');

        $serviceGameReleaseDate = $serviceContainer->getGameReleaseDateService();
        $serviceTopRated = $serviceContainer->getTopRatedService();

        $bindings = [];

        $bindings['NewReleases'] = $serviceGameReleaseDate->getReleased($regionCode, 21);
        $bindings['UpcomingReleases'] = $serviceGameReleaseDate->getUpcoming($regionCode, 21);
        //$bindings['TopRatedAllTime'] = $serviceTopRated->getList($regionCode, 15);

        $bindings['CalendarThisMonth'] = date('Y-m');

        $bindings['TopTitle'] = 'Nintendo Switch games list';
        $bindings['PageTitle'] = 'Nintendo Switch games list';

        return view('games.landing', $bindings);
    }

    public function listReleased()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $regionCode = \Request::get('regionCode');

        $serviceGameReleaseDate = $serviceContainer->getGameReleaseDateService();

        $bindings = [];

        $gamesList = $serviceGameReleaseDate->getReleased($regionCode);

        $bindings['GamesList'] = $gamesList;
        $bindings['GamesTableSort'] = "[[3, 'desc'], [1, 'asc']]";

        $bindings['TopTitle'] = 'Nintendo Switch released games';
        $bindings['PageTitle'] = 'Nintendo Switch released games';

        return view('games.list.releasedGames', $bindings);
    }

    public function listUpcoming()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $regionCode = \Request::get('regionCode');

        $serviceGameReleaseDate = $serviceContainer->getGameReleaseDateService();

        $bindings = [];

        $bindings['UpcomingGames'] = $serviceGameReleaseDate->getUpcoming($regionCode);

        $featuredIdList = [
            1237, // Ninjala
            319, // Yoshi's Crafted World
            1544, // Final Fantasy XII
            1224, // Killer Queen Black
            86, // Fire Emblem Three Houses
            1222, // Daemon X Machina
        ];
        $bindings['FeaturedGames'] = $serviceGameReleaseDate->getByIdList($featuredIdList, $regionCode);

        $bindings['TopTitle'] = 'Nintendo Switch upcoming games';
        $bindings['PageTitle'] = 'Upcoming Nintendo Switch games';

        return view('games.list.upcomingGames', $bindings);
    }

    public function listUnreleased()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $regionCode = \Request::get('regionCode');

        $serviceGameReleaseDate = $serviceContainer->getGameReleaseDateService();

        $bindings = [];

        $upcomingLists = [];

        $upcomingLists[] = [
            'MainTitle' => 'Unreleased in this region',
            'ShortTitle' => 'Unreleased',
            'List' => $serviceGameReleaseDate->getUnreleased($regionCode)
        ];

        $bindings['UpcomingLists'] = $upcomingLists;

        $bindings['TopTitle'] = 'Nintendo Switch unreleased games';
        $bindings['PageTitle'] = 'Unreleased Nintendo Switch games';

        return view('games.list.unreleasedGames', $bindings);
    }

    public function listTopRated()
    {
        return redirect(route('topRated.allTime'), 301);
    }

    public function listReviewsNeeded()
    {
        return redirect(route('reviews.landing'), 301);
    }

    public function genresLanding()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $serviceGenre = $serviceContainer->getGenreService();

        $bindings = [];

        $genreList = $serviceGenre->getAll();

        $bindings['GenreList'] = $genreList;

        $bindings['TopTitle'] = 'Nintendo Switch - Game genres';
        $bindings['PageTitle'] = 'Nintendo Switch game genres';

        return view('games.genres.landing', $bindings);
    }

    public function genreByName($linkTitle)
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $regionCode = \Request::get('regionCode');

        $serviceGenre = $serviceContainer->getGenreService();
        $serviceGameGenre = $serviceContainer->getGameGenreService();

        $genreData = $serviceGenre->getByLinkTitle($linkTitle);

        if (!$genreData) {
            abort(404);
        }

        $bindings = [];

        $bindings['GenreData'] = $genreData;

        $bindings['GamesList'] = $serviceGameGenre->getGamesByGenre($regionCode, $genreData->id);
        $bindings['GamesTableSort'] = "[1, 'asc']";

        $bindings['TopTitle'] = 'Nintendo Switch games in genre: '.$genreData->genre;
        $bindings['PageTitle'] = 'Nintendo Switch games in genre: '.$genreData->genre;

        return view('games.genres.item', $bindings);
    }

    public function gamesOnSale()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $regionCode = \Request::get('regionCode');

        $serviceEshopEuropeGame = $serviceContainer->getEshopEuropeGameService();
        $gamesOnSale = $serviceEshopEuropeGame->getGamesOnSale();

        $bindings = [];

        $bindings['RegionCode'] = $regionCode;

        $bindings['GamesList'] = $gamesOnSale;

        $bindings['TopTitle'] = 'Nintendo Switch games currently on sale in Europe';
        $bindings['PageTitle'] = 'Nintendo Switch games currently on sale in Europe';

        return view('games.on-sale.gamesOnSale', $bindings);
    }

    /**
     * @param $gameId
     * @param $linkTitle
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
     */
    public function show($gameId, $linkTitle)
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $regionCode = \Request::get('regionCode');

        $serviceGame = $serviceContainer->getGameService();
        $serviceTopRated = $serviceContainer->getTopRatedService();
        $serviceChartsRankingGlobal = $serviceContainer->getChartsRankingGlobalService();
        $serviceReviewLink = $serviceContainer->getReviewLinkService();
        $serviceGameReleaseDate = $serviceContainer->getGameReleaseDateService();
        $serviceGameGenres = $serviceContainer->getGameGenreService();
        $serviceReviewUser = $serviceContainer->getReviewUserService();
        $serviceGameDeveloper = $serviceContainer->getGameDeveloperService();
        $serviceGamePublisher = $serviceContainer->getGamePublisherService();
        $serviceGameTag = $serviceContainer->getGameTagService();

        $bindings = [];

        $gameData = $serviceGame->find($gameId);
        if (!$gameData) {
            abort(404);
        }

        if ($gameData->link_title != $linkTitle) {
            $redirUrl = sprintf('/games/%s/%s', $gameId, $gameData->link_title);
            return redirect($redirUrl, 301);
        }

        // Get chart rankings for this game
        $gameRanking = $serviceChartsRankingGlobal->getByGameEu($gameId);

        // Get reviews
        $gameReviews = $serviceReviewLink->getByGame($gameId);

        // Get genres
        $gameGenres = $serviceGameGenres->getByGame($gameId);

        // Get user reviews
        $gameUserReviews = $serviceReviewUser->getByGame($gameId);

        // Get game metadata
        $gameDevelopers = $serviceGameDeveloper->getByGame($gameId);
        $gamePublishers = $serviceGamePublisher->getByGame($gameId);
        $gameTags = $serviceGameTag->getByGame($gameId);

        $bindings['TopTitle'] = $gameData->title.' - Nintendo Switch game details';
        $bindings['PageTitle'] = $gameData->title;
        $bindings['GameId'] = $gameId;
        $bindings['GameData'] = $gameData;
        $bindings['GameRanking'] = $gameRanking;
        $bindings['GameReviews'] = $gameReviews;
        $bindings['GameGenres'] = $gameGenres;
        $bindings['GameReviewUserList'] = $gameUserReviews;
        $bindings['GameDevelopers'] = $gameDevelopers;
        $bindings['GamePublishers'] = $gamePublishers;
        $bindings['GameTags'] = $gameTags;

        $bindings['ReleaseDates'] = $serviceGameReleaseDate->getByGame($gameId);

        // Total rank count
        $bindings['RankMaximum'] = $serviceTopRated->getCount($regionCode);

        // Check if game is on lists
        if (Auth::id()) {
            $userId = Auth::id();

            $userListService = $serviceContainer->getUserListService();
            $userListItemService = $serviceContainer->getUserListItemService();

            $ownedList = $userListService->getOwnedListByUser($userId);
            $wishList = $userListService->getWishListByUser($userId);

            if ($ownedList) {
                $listId = $ownedList->id;
                $gameOnOwnedList = $userListItemService->getByListAndGame($listId, $gameId);
                $bindings['IsOnOwnedList'] = !is_null($gameOnOwnedList) ? 'Y' : 'N';
            }
            if ($wishList) {
                $listId = $wishList->id;
                $gameOnWishList = $userListItemService->getByListAndGame($listId, $gameId);
                $bindings['IsOnWishList'] = !is_null($gameOnWishList) ? 'Y' : 'N';
            }

        }

        return view('games.page.show', $bindings);
    }

    /**
     * This is for redirecting old links. Do not use for new links.
     * @param integer $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function showId($id)
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $serviceGame = $serviceContainer->getGameService();

        $gameData = $serviceGame->find($id);
        if (!$gameData) {
            abort(404);
        }

        $redirUrl = sprintf('/games/%s/%s', $id, $gameData->link_title);
        return redirect($redirUrl, 301);
    }

}
