<?php

namespace App\Http\Controllers;

use Illuminate\Support\Collection;

use Auth;
use App\Services\UserListService;
use App\Services\UserListItemService;

class GamesController extends BaseController
{
    public function listReleased()
    {
        $bindings = array();

        $gamesList = $this->serviceGame->getListReleased();

        $bindings['GamesList'] = $gamesList;
        $bindings['GamesTableSort'] = "[[3, 'desc'], [1, 'asc']]";

        $bindings['TopTitle'] = 'Nintendo Switch released games';
        $bindings['PageTitle'] = 'Nintendo Switch games available in Europe';

        return view('games.list.releasedGames', $bindings);
    }

    public function listUpcoming()
    {
        $bindings = array();

        // Current/Most active year
        $bindings['Upcoming2018WithDates'] = $this->serviceGame->getAllUpcomingYearWithDates(2018);
        $bindings['Upcoming2018WithQuarter'] = $this->serviceGame->getAllUpcomingYearQuarters(2018);
        $bindings['Upcoming2018NoInfo'] = $this->serviceGame->getAllUpcomingYearXs(2018);

        // Longer term / TBA
        $bindings['UpcomingFuture'] = $this->serviceGame->getAllUpcomingFuture(2018);
        $bindings['UpcomingTBA'] = $this->serviceGame->getAllUpcomingTBA();

        // Needed for overall total
        $bindings['UpcomingGamesFullList'] = $this->serviceGame->getAllUpcoming();

        $bindings['TopTitle'] = 'Nintendo Switch upcoming games';
        $bindings['PageTitle'] = 'Upcoming Nintendo Switch games';

        return view('games.list.upcomingGames', $bindings);
    }

    public function listTopRated()
    {
        return redirect(route('reviews.topRated.allTime'), 301);
    }

    public function listReviewsNeeded()
    {
        return redirect(route('reviews.gamesNeedingReviews'), 301);
    }

    public function genresLanding()
    {
        $bindings = array();

        $serviceGenre = resolve('Services\GenreService');
        $genreList = $serviceGenre->getAll();

        $bindings['GenreList'] = $genreList;

        $bindings['TopTitle'] = 'Nintendo Switch - Game genres';
        $bindings['PageTitle'] = 'Nintendo Switch game genres';

        return view('games.genres.landing', $bindings);
    }

    public function genreByName($linkTitle)
    {
        $bindings = array();

        $serviceGenre = resolve('Services\GenreService');
        $genreData = $serviceGenre->getByLinkTitle($linkTitle);

        if (!$genreData) {
            abort(404);
        }

        $bindings['GenreData'] = $genreData;

        $bindings['GamesList'] = $this->serviceGame->getGamesByGenre($genreData->id);
        $bindings['GamesTableSort'] = "[1, 'asc']";

        $bindings['TopTitle'] = 'Nintendo Switch games in genre: '.$genreData->genre;
        $bindings['PageTitle'] = 'Nintendo Switch games in genre: '.$genreData->genre;

        return view('games.genres.item', $bindings);
    }

    /**
     * @param $id
     * @param $linkTitle
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
     */
    public function show($gameId, $linkTitle)
    {
        $bindings = array();

        $gameData = $this->serviceGame->find($gameId);
        if (!$gameData) {
            abort(404);
        }

        if ($gameData->link_title != $linkTitle) {
            $redirUrl = sprintf('/games/%s/%s', $gameId, $gameData->link_title);
            return redirect($redirUrl, 301);
        }

        $chartsRankingGlobalService = resolve('Services\ChartsRankingGlobalService');
        /* @var $chartsRankingGlobalService \App\Services\ChartsRankingGlobalService */
        // Get chart rankings for this game
        $gameRanking = $chartsRankingGlobalService->getByGameEu($gameId);

        // Get reviews
        $reviewLinkService = resolve('Services\ReviewLinkService');
        $gameReviews = $reviewLinkService->getByGame($gameId);

        $bindings['TopTitle'] = $gameData->title;
        $bindings['PageTitle'] = $gameData->title.' - Nintendo Switch game details';
        $bindings['GameId'] = $gameId;
        $bindings['GameData'] = $gameData;
        $bindings['GameRanking'] = $gameRanking;
        $bindings['GameReviews'] = $gameReviews;

        // Total rank count
        $bindings['RankMaximum'] = $this->serviceGame->getListTopRatedCount();

        // Check if game is on lists
        if (Auth::id()) {
            $userId = Auth::id();

            $userListService = resolve('Services\UserListService');
            $userListItemService = resolve('Services\UserListItemService');
            /* @var $userListService UserListService */
            /* @var $userListItemService UserListItemService */

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

        return view('games.show', $bindings);
    }

    /**
     * This is for redirecting old links. Do not use for new links.
     * @param integer $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function showId($id)
    {
        $gameData = $this->serviceGame->find($id);
        if (!$gameData) {
            abort(404);
        }

        $redirUrl = sprintf('/games/%s/%s', $id, $gameData->link_title);
        return redirect($redirUrl, 301);
    }

}
