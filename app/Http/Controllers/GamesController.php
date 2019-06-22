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

        $bindings = [];

        $bindings['NewReleases'] = $serviceGameReleaseDate->getReleased($regionCode, 20);
        $bindings['UpcomingReleases'] = $serviceGameReleaseDate->getUpcoming($regionCode, 20);

        $bindings['CalendarThisMonth'] = date('Y-m');

        $bindings['TopTitle'] = 'Nintendo Switch games database';
        $bindings['PageTitle'] = 'Nintendo Switch games database';

        return view('games.landing', $bindings);
    }

    public function recentReleases()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $regionCode = \Request::get('regionCode');

        $serviceGameReleaseDate = $serviceContainer->getGameReleaseDateService();

        $bindings = [];

        $bindings['NewReleases'] = $serviceGameReleaseDate->getReleased($regionCode, 50);

        $bindings['TopTitle'] = 'Nintendo Switch recent releases';
        $bindings['PageTitle'] = 'Nintendo Switch recent releases';

        return view('games.recentReleases', $bindings);
    }

    public function upcomingReleases()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $regionCode = \Request::get('regionCode');

        $serviceGameReleaseDate = $serviceContainer->getGameReleaseDateService();

        $bindings = [];

        $bindings['UpcomingGames'] = $serviceGameReleaseDate->getUpcoming($regionCode);

        $featuredIdList = [
            1237, // Ninjala
            1224, // Killer Queen Black
            86, // Fire Emblem Three Houses
            1222, // Daemon X Machina
            2126, // Super Mario Maker 2
            1487, // Dragon Quest Builders 2
            2146, // Astral Chain
            2147, // Dragon Quest XI S
            2578, // Zelda Link's Awakening
            2148, // Marvel Ultimate Alliance
        ];
        $bindings['FeaturedGames'] = $serviceGameReleaseDate->getByIdList($featuredIdList, $regionCode);

        $bindings['TopTitle'] = 'Nintendo Switch upcoming games';
        $bindings['PageTitle'] = 'Upcoming Nintendo Switch games';

        return view('games.upcomingReleases', $bindings);
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

        return view('games.gamesOnSale', $bindings);
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
        $serviceGameRankAllTime = $serviceContainer->getGameRankAllTimeService();
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

        // Get reviews
        $gameReviews = $serviceReviewLink->getByGame($gameId);

        // Get genres
        $gameGenres = $serviceGameGenres->getByGame($gameId);

        // Get user reviews
        $gameUserReviews = $serviceReviewUser->getActiveByGame($gameId);

        // Get game metadata
        $gameDevelopers = $serviceGameDeveloper->getByGame($gameId);
        $gamePublishers = $serviceGamePublisher->getByGame($gameId);
        $gameTags = $serviceGameTag->getByGame($gameId);

        $bindings['TopTitle'] = $gameData->title.' - Nintendo Switch game ratings, reviews and information';
        $bindings['PageTitle'] = $gameData->title;
        $bindings['GameId'] = $gameId;
        $bindings['GameData'] = $gameData;
        $bindings['GameReviews'] = $gameReviews;
        $bindings['GameGenres'] = $gameGenres;
        $bindings['GameReviewUserList'] = $gameUserReviews;
        $bindings['GameDevelopers'] = $gameDevelopers;
        $bindings['GamePublishers'] = $gamePublishers;
        $bindings['GameTags'] = $gameTags;

        $bindings['ReleaseDates'] = $serviceGameReleaseDate->getByGame($gameId);

        $bindings['ReleaseDateInfo'] = $serviceGameReleaseDate->getByGameAndRegion($gameId, $regionCode);

        // Total rank count
        $bindings['RankMaximum'] = $serviceGameRankAllTime->countRanked();

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
