<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;

class GamesController extends Controller
{
    use SwitchServices;

    public function landing()
    {
        $serviceGameReleaseDate = $this->getServiceGameReleaseDate();

        $bindings = [];

        $bindings['NewReleases'] = $serviceGameReleaseDate->getReleased(20);
        $bindings['UpcomingReleases'] = $serviceGameReleaseDate->getUpcoming(20);

        $bindings['CalendarThisMonth'] = date('Y-m');

        $bindings['TopTitle'] = 'Nintendo Switch games database';
        $bindings['PageTitle'] = 'Nintendo Switch games database';

        return view('games.landing', $bindings);
    }

    public function recentReleases()
    {
        $serviceGameReleaseDate = $this->getServiceGameReleaseDate();

        $bindings = [];

        $bindings['NewReleases'] = $serviceGameReleaseDate->getReleased(50);
        $bindings['CalendarThisMonth'] = date('Y-m');

        $bindings['TopTitle'] = 'Nintendo Switch recent releases';
        $bindings['PageTitle'] = 'Nintendo Switch recent releases';

        return view('games.recentReleases', $bindings);
    }

    public function upcomingReleases()
    {
        $serviceGameReleaseDate = $this->getServiceGameReleaseDate();

        $bindings = [];

        $bindings['UpcomingGames'] = $serviceGameReleaseDate->getUpcoming();

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
        $bindings['FeaturedGames'] = $serviceGameReleaseDate->getByIdList($featuredIdList);

        $bindings['TopTitle'] = 'Nintendo Switch upcoming games';
        $bindings['PageTitle'] = 'Upcoming Nintendo Switch games';

        return view('games.upcomingReleases', $bindings);
    }

    public function gamesOnSale()
    {
        $bindings = [];

        $bindings['TopTitle'] = 'Nintendo Switch games currently on sale in Europe';
        $bindings['PageTitle'] = 'Nintendo Switch games currently on sale in Europe';

        $bindings['HighestDiscounts'] = $this->getServiceDataSourceParsed()->getGamesOnSaleHighestDiscounts(50);
        $bindings['GoodRanks'] = $this->getServiceDataSourceParsed()->getGamesOnSaleGoodRanks(50);
        $bindings['UnrankedDiscounts'] = $this->getServiceDataSourceParsed()->getGamesOnSaleUnranked(50);

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
        $serviceGame = $this->getServiceGame();
        $serviceReviewLink = $this->getServiceReviewLink();
        $serviceGameGenres = $this->getServiceGameGenre();
        $serviceQuickReview = $this->getServiceQuickReview();
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
        $gameQuickReviews = $serviceQuickReview->getActiveByGame($gameId);

        // Get game metadata
        $gameDevelopers = $serviceGameDeveloper->getByGame($gameId);
        $gamePublishers = $serviceGamePublisher->getByGame($gameId);
        $gameTags = $serviceGameTag->getByGame($gameId);

        // Data sources
        $dsNintendoCoUk = $this->getServiceDataSourceParsed()->getSourceNintendoCoUkForGame($gameId);
        $bindings['DSNintendoCoUk'] = $dsNintendoCoUk;

        // News
        $bindings['GameNews'] = $this->getServiceNews()->getByGameId($gameId, 10);

        $bindings['TopTitle'] = $gameData->title.' - Nintendo Switch game ratings, reviews and information';
        $bindings['PageTitle'] = $gameData->title;
        $bindings['GameId'] = $gameId;
        $bindings['GameData'] = $gameData;
        $bindings['GameReviews'] = $gameReviews;
        $bindings['GameGenres'] = $gameGenres;
        $bindings['GameQuickReviewList'] = $gameQuickReviews;
        $bindings['GameDevelopers'] = $gameDevelopers;
        $bindings['GamePublishers'] = $gamePublishers;
        $bindings['GameTags'] = $gameTags;

        // Total rank count
        $bindings['RankMaximum'] = $serviceGame->countRanked();

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
