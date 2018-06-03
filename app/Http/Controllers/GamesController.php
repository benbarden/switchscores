<?php

namespace App\Http\Controllers;

use Illuminate\Support\Collection;

use Auth;
use App\Services\CalendarService;
use App\Services\ChartsRankingGlobalService;
use App\Services\GameGenreService;
use App\Services\GameReleaseDateService;
use App\Services\GenreService;
use App\Services\ReviewLinkService;
use App\Services\TopRatedService;
use App\Services\UserListService;
use App\Services\UserListItemService;

class GamesController extends BaseController
{
    public function landing()
    {
        $serviceGameReleaseDate = resolve('Services\GameReleaseDateService');
        /* @var $serviceGameReleaseDate GameReleaseDateService */
        $serviceTopRated = resolve('Services\TopRatedService');
        /* @var $serviceTopRated TopRatedService */

        $bindings = [];

        $bindings['NewReleases'] = $serviceGameReleaseDate->getReleased($this->region, 15);
        $bindings['UpcomingReleases'] = $serviceGameReleaseDate->getUpcoming($this->region, 15);
        $bindings['TopRatedAllTime'] = $serviceTopRated->getList($this->region, 15);

        $bindings['CalendarThisMonth'] = date('Y-m');

        $bindings['TopTitle'] = 'List of Nintendo Switch games';
        $bindings['PageTitle'] = 'Games';

        return view('games.landing', $bindings);
    }

    public function listReleased()
    {
        $serviceGameReleaseDate = resolve('Services\GameReleaseDateService');
        /* @var $serviceGameReleaseDate GameReleaseDateService */

        $bindings = [];

        $gamesList = $serviceGameReleaseDate->getReleased($this->region);

        $bindings['GamesList'] = $gamesList;
        $bindings['GamesTableSort'] = "[[3, 'desc'], [1, 'asc']]";

        $bindings['TopTitle'] = 'Nintendo Switch released games';
        $bindings['PageTitle'] = 'Nintendo Switch games available in Europe';

        return view('games.list.releasedGames', $bindings);
    }

    public function listUpcoming()
    {
        $serviceGameReleaseDate = resolve('Services\GameReleaseDateService');
        /* @var $serviceGameReleaseDate GameReleaseDateService */

        $bindings = [];

        $upcomingLists = [];

        // Current/Most active year
        $upcomingLists[] = [
            'MainTitle' => '2018 games with release dates',
            'ShortTitle' => '2018 with dates',
            'List' => $serviceGameReleaseDate->getUpcomingYearWithDates(2018, $this->region)
        ];
        $upcomingLists[] = [
            'MainTitle' => '2018 games, quarter only',
            'ShortTitle' => '2018 with quarters',
            'List' => $serviceGameReleaseDate->getUpcomingYearQuarters(2018, $this->region)
        ];
        $upcomingLists[] = [
            'MainTitle' => '2018 games, no firm date',
            'ShortTitle' => '2018 sometime',
            'List' => $serviceGameReleaseDate->getUpcomingYearXs(2018, $this->region)
        ];

        // Longer term/TBA
        $upcomingLists[] = [
            'MainTitle' => 'Beyond 2018',
            'ShortTitle' => 'Beyond 2018',
            'List' => $serviceGameReleaseDate->getUpcomingFuture(2018, $this->region)
        ];
        $upcomingLists[] = [
            'MainTitle' => 'TBA - no date given',
            'ShortTitle' => 'TBA',
            'List' => $serviceGameReleaseDate->getUpcomingTBA($this->region)
        ];

        $bindings['UpcomingLists'] = $upcomingLists;

        // Needed for overall total
        $bindings['UpcomingGamesCount'] = $serviceGameReleaseDate->countUpcoming($this->region);

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

    private function getAllowedDates()
    {
        $dates = [];

        for ($i=2017; $i<date('Y')+1; $i++) {

            for ($j=1; $j<13; $j++) {

                // Start from March 2017
                if ($i == 2017 && $j < 3) continue;
                // Don't go beyond the current month and year
                if ($i == date('Y') && $j > date('m')) break;
                // Good to go
                $dateToAdd = $i.'-'.str_pad($j, 2, '0', STR_PAD_LEFT);
                $dates[] = $dateToAdd;

            }

        }

        $dates = array_reverse($dates);

        return $dates;
    }

    public function calendarLanding()
    {
        $bindings = [];

        $bindings['TopTitle'] = 'Nintendo Switch - Release calendar';
        $bindings['PageTitle'] = 'Release calendar';

        $bindings['DateList'] = $this->getAllowedDates();

        return view('games.calendar.landing', $bindings);
    }

    public function calendarPage($date)
    {
        $serviceCalendar = resolve('Services\CalendarService');
        /* @var $serviceCalendar CalendarService */

        $dates = $this->getAllowedDates();
        if (!in_array($date, $dates)) {
            abort(404);
        }

        $bindings = [];

        $dtDate = new \DateTime($date);
        $dtDateDesc = $dtDate->format('M Y');

        $calendarYear = $dtDate->format('Y');
        $calendarMonth = $dtDate->format('m');
        $bindings['GamesByMonthList'] = $serviceCalendar->getList($this->region, $calendarYear, $calendarMonth);
        $bindings['GamesByMonthRatings'] = $serviceCalendar->getRatings($this->region, $calendarYear, $calendarMonth);

        $bindings['TopTitle'] = 'Nintendo Switch - Release calendar: '.$dtDateDesc;
        $bindings['PageTitle'] = 'Release calendar: '.$dtDateDesc;

        return view('games.calendar.page', $bindings);
    }

    public function genresLanding()
    {
        $serviceGenre = resolve('Services\GenreService');
        /* @var $serviceGenre GenreService */

        $bindings = [];

        $genreList = $serviceGenre->getAll();

        $bindings['GenreList'] = $genreList;

        $bindings['TopTitle'] = 'Nintendo Switch - Game genres';
        $bindings['PageTitle'] = 'Nintendo Switch game genres';

        return view('games.genres.landing', $bindings);
    }

    public function genreByName($linkTitle)
    {
        $serviceGenre = resolve('Services\GenreService');
        /* @var $serviceGenre GenreService */
        $serviceGameGenre = resolve('Services\GameGenreService');
        /* @var $serviceGameGenre GameGenreService */

        $genreData = $serviceGenre->getByLinkTitle($linkTitle);

        if (!$genreData) {
            abort(404);
        }

        $bindings = [];

        $bindings['GenreData'] = $genreData;

        $bindings['GamesList'] = $serviceGameGenre->getGamesByGenre($this->region, $genreData->id);
        $bindings['GamesTableSort'] = "[1, 'asc']";

        $bindings['TopTitle'] = 'Nintendo Switch games in genre: '.$genreData->genre;
        $bindings['PageTitle'] = 'Nintendo Switch games in genre: '.$genreData->genre;

        return view('games.genres.item', $bindings);
    }

    /**
     * @param $gameId
     * @param $linkTitle
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
     */
    public function show($gameId, $linkTitle)
    {
        $serviceTopRated = resolve('Services\TopRatedService');
        /* @var $serviceTopRated TopRatedService */
        $chartsRankingGlobalService = resolve('Services\ChartsRankingGlobalService');
        /* @var $chartsRankingGlobalService ChartsRankingGlobalService */
        $reviewLinkService = resolve('Services\ReviewLinkService');
        /* @var $reviewLinkService ReviewLinkService */
        $serviceGameReleaseDate = resolve('Services\GameReleaseDateService');
        /* @var $serviceGameReleaseDate GameReleaseDateService */

        $bindings = [];

        $gameData = $this->serviceGame->find($gameId);
        if (!$gameData) {
            abort(404);
        }

        if ($gameData->link_title != $linkTitle) {
            $redirUrl = sprintf('/games/%s/%s', $gameId, $gameData->link_title);
            return redirect($redirUrl, 301);
        }

        // Get chart rankings for this game
        $gameRanking = $chartsRankingGlobalService->getByGameEu($gameId);

        // Get reviews
        $gameReviews = $reviewLinkService->getByGame($gameId);

        $bindings['TopTitle'] = $gameData->title;
        $bindings['PageTitle'] = $gameData->title.' - Nintendo Switch game details';
        $bindings['GameId'] = $gameId;
        $bindings['GameData'] = $gameData;
        $bindings['GameRanking'] = $gameRanking;
        $bindings['GameReviews'] = $gameReviews;

        $bindings['ReleaseDates'] = $serviceGameReleaseDate->getByGame($gameId);

        // Total rank count
        $bindings['RankMaximum'] = $serviceTopRated->getCount($this->region);

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
