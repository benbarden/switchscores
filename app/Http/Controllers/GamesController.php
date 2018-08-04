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

        $bindings['NewReleases'] = $serviceGameReleaseDate->getReleased($regionCode, 15);
        $bindings['UpcomingReleases'] = $serviceGameReleaseDate->getUpcoming($regionCode, 15);
        $bindings['TopRatedAllTime'] = $serviceTopRated->getList($regionCode, 15);

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

        $upcomingLists = [];

        // Current/Most active year
        $upcomingLists[] = [
            'MainTitle' => '2018 games with release dates',
            'ShortTitle' => '2018 with dates',
            'List' => $serviceGameReleaseDate->getUpcomingYearWithDates(2018, $regionCode)
        ];
        $upcomingLists[] = [
            'MainTitle' => '2018 games, quarter only',
            'ShortTitle' => '2018 with quarters',
            'List' => $serviceGameReleaseDate->getUpcomingYearQuarters(2018, $regionCode)
        ];
        $upcomingLists[] = [
            'MainTitle' => '2018 games, no firm date',
            'ShortTitle' => '2018 sometime',
            'List' => $serviceGameReleaseDate->getUpcomingYearXs(2018, $regionCode)
        ];

        // Longer term/TBA
        $upcomingLists[] = [
            'MainTitle' => 'Beyond 2018',
            'ShortTitle' => 'Beyond 2018',
            'List' => $serviceGameReleaseDate->getUpcomingFuture(2018, $regionCode)
        ];
        $upcomingLists[] = [
            'MainTitle' => 'TBA - no date given',
            'ShortTitle' => 'TBA',
            'List' => $serviceGameReleaseDate->getUpcomingTBA($regionCode)
        ];

        $bindings['UpcomingLists'] = $upcomingLists;

        // Needed for overall total
        $bindings['UpcomingGamesCount'] = $serviceGameReleaseDate->countUpcoming($regionCode);

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
        return redirect(route('reviews.topRated.allTime'), 301);
    }

    public function listReviewsNeeded()
    {
        return redirect(route('reviews.gamesNeedingReviews'), 301);
    }

    public function calendarLanding()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $regionCode = \Request::get('regionCode');
        $regionCodeDesc = null;
        switch ($regionCode) {
            case 'eu':
                $regionCodeDesc = 'Europe';
                break;
            case 'us':
                $regionCodeDesc = 'US';
                break;
            case 'jp':
                $regionCodeDesc = 'Japan';
                break;
            default:
                break;
        }

        $serviceGameCalendar = $serviceContainer->getGameCalendarService();

        $bindings = [];

        if ($regionCodeDesc) {
            $bindings['RegionCodeDesc'] = $regionCodeDesc;
        }

        $bindings['TopTitle'] = 'Nintendo Switch - Release calendar';
        $bindings['PageTitle'] = 'Nintendo Switch - Release calendar';

        $dateList = $serviceGameCalendar->getAllowedDates();
        $dateListArray = [];

        if ($dateList) {

            foreach ($dateList as $date) {

                list($dateYear, $dateMonth) = explode('-', $date);

                $gameCalendarStat = $serviceGameCalendar->getStat($regionCode, $dateYear, $dateMonth);
                $dateCount = $gameCalendarStat->released_count;

                $dateListArray[] = [
                    'DateRaw' => $date,
                    'GameCount' => $dateCount,
                ];

            }

        }

        $bindings['DateList'] = $dateListArray;

        return view('games.calendar.landing', $bindings);
    }

    public function calendarPage($date)
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $regionCode = \Request::get('regionCode');

        $serviceGameCalendar = $serviceContainer->getGameCalendarService();

        $dates = $serviceGameCalendar->getAllowedDates();
        if (!in_array($date, $dates)) {
            abort(404);
        }

        $bindings = [];

        $dtDate = new \DateTime($date);
        $dtDateDesc = $dtDate->format('M Y');

        $calendarYear = $dtDate->format('Y');
        $calendarMonth = $dtDate->format('m');
        $bindings['GamesByMonthList'] = $serviceGameCalendar->getList($regionCode, $calendarYear, $calendarMonth);
        $bindings['GamesByMonthRatings'] = $serviceGameCalendar->getRatings($regionCode, $calendarYear, $calendarMonth);

        $bindings['TopTitle'] = 'Nintendo Switch - Release calendar: '.$dtDateDesc;
        $bindings['PageTitle'] = 'Nintendo Switch - Release calendar: '.$dtDateDesc;

        return view('games.calendar.page', $bindings);
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

        $bindings['TopTitle'] = $gameData->title.' - Nintendo Switch game details';
        $bindings['PageTitle'] = $gameData->title;
        $bindings['GameId'] = $gameId;
        $bindings['GameData'] = $gameData;
        $bindings['GameRanking'] = $gameRanking;
        $bindings['GameReviews'] = $gameReviews;
        $bindings['GameGenres'] = $gameGenres;

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

        return view('games.show', $bindings);
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
