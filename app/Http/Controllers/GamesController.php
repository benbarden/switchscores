<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as Controller;

use App\Services\ServiceContainer;

use App\Traits\SiteRequestData;
use App\Traits\WosServices;

use Auth;

class GamesController extends Controller
{
    use WosServices;
    use SiteRequestData;

    public function landing()
    {
        $regionCode = $this->getRegionCode();

        $serviceGameReleaseDate = $this->getServiceGameReleaseDate();

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
        $regionCode = $this->getRegionCode();

        $serviceGameReleaseDate = $this->getServiceGameReleaseDate();

        $bindings = [];

        $bindings['NewReleases'] = $serviceGameReleaseDate->getReleased($regionCode, 50);
        $bindings['CalendarThisMonth'] = date('Y-m');

        $bindings['TopTitle'] = 'Nintendo Switch recent releases';
        $bindings['PageTitle'] = 'Nintendo Switch recent releases';

        return view('games.recentReleases', $bindings);
    }

    public function upcomingReleases()
    {
        $regionCode = $this->getRegionCode();

        $serviceGameReleaseDate = $this->getServiceGameReleaseDate();

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
        $regionCode = $this->getRegionCode();

        $serviceEshopEuropeGame = $this->getServiceEshopEuropeGame();
        //$gamesOnSale = $serviceEshopEuropeGame->getGamesOnSale();

        $bindings = [];

        $bindings['RegionCode'] = $regionCode;

        $bindings['TopTitle'] = 'Nintendo Switch games currently on sale in Europe';
        $bindings['PageTitle'] = 'Nintendo Switch games currently on sale in Europe';

        $bindings['HighestDiscounts'] = $serviceEshopEuropeGame->getGamesOnSaleHighestDiscounts(50);
        $bindings['GoodRanks'] = $serviceEshopEuropeGame->getGamesOnSaleGoodRanks(50);
        $bindings['UnrankedDiscounts'] = $serviceEshopEuropeGame->getGamesOnSaleUnranked(50);

        //$bindings['AllGamesOnSale'] = $gamesOnSale;

        return view('games.gamesOnSale', $bindings);
    }

    /**
     * @param $gameId
     * @param $linkTitle
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
     */
    public function show($gameId, $linkTitle)
    {
        $regionCode = $this->getRegionCode();

        $serviceGame = $this->getServiceGame();
        $serviceGameRankAllTime = $this->getServiceGameRankAllTime();
        $serviceReviewLink = $this->getServiceReviewLink();
        $serviceGameReleaseDate = $this->getServiceGameReleaseDate();
        $serviceGameGenres = $this->getServiceGameGenre();
        $serviceReviewUser = $this->getServiceReviewUser();
        $serviceGameDeveloper = $this->getServiceGameDeveloper();
        $serviceGamePublisher = $this->getServiceGamePublisher();
        $serviceGameTag = $this->getServiceGameTag();

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
        $serviceGame = $this->getServiceGame();

        $gameData = $serviceGame->find($id);
        if (!$gameData) {
            abort(404);
        }

        $redirUrl = sprintf('/games/%s/%s', $id, $gameData->link_title);
        return redirect($redirUrl, 301);
    }

}
