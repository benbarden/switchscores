<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;
use App\Traits\AuthUser;

class GamesController extends Controller
{
    use SwitchServices;
    use AuthUser;

    public function landing()
    {
        $bindings = [];

        $serviceGameReleaseDate = $this->getServiceGameReleaseDate();

        $bindings['NewReleases'] = $serviceGameReleaseDate->getReleased(20);
        $bindings['UpcomingReleases'] = $serviceGameReleaseDate->getUpcoming(30);

        $bindings['RecentWithGoodRanks'] = $this->getServiceGameReleaseDate()->getRecentWithGoodRanks(7, 35, 15);
        $bindings['HighlightsRecentlyRanked'] = $this->getServiceReviewLink()->getHighlightsRecentlyRanked();
        $bindings['HighlightsStillUnranked'] = $this->getServiceReviewLink()->getHighlightsStillUnranked();
        $bindings['TopRatedDiscounts'] = $this->getServiceDataSourceParsed()->getGamesOnSaleGoodRanks(50);

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
        $bindings = [];

        $gameData = $this->getServiceGame()->find($gameId);
        if (!$gameData) {
            abort(404);
        }

        if ($gameData->link_title != $linkTitle) {
            $redirUrl = sprintf('/games/%s/%s', $gameId, $gameData->link_title);
            return redirect($redirUrl, 301);
        }

        // Main data
        $bindings['TopTitle'] = $gameData->title.' - Nintendo Switch game ratings, reviews and information';
        $bindings['PageTitle'] = $gameData->title;
        $bindings['GameId'] = $gameId;
        $bindings['GameData'] = $gameData;
        $bindings['GameReviews'] = $this->getServiceReviewLink()->getByGame($gameId);
        $bindings['GameQuickReviewList'] = $this->getServiceQuickReview()->getActiveByGame($gameId);
        $bindings['GameDevelopers'] = $this->getServiceGameDeveloper()->getByGame($gameId);
        $bindings['GamePublishers'] = $this->getServiceGamePublisher()->getByGame($gameId);
        $bindings['GameTags'] = $this->getServiceGameTag()->getByGame($gameId);

        // Data sources
        $bindings['DSNintendoCoUk'] = $this->getServiceDataSourceParsed()->getSourceNintendoCoUkForGame($gameId);

        // News
        $bindings['GameNews'] = $this->getServiceNews()->getByGameId($gameId, 10);

        // Total rank count
        $bindings['RankMaximum'] = $this->getServiceGame()->countRanked();

        // Logged in user data
        $userId = $this->getAuthId();
        if ($userId) {
            $bindings['UserCollectionItem'] = $this->getServiceUserGamesCollection()->getUserGameItem($userId, $gameId);
            $bindings['UserCollectionGame'] = $gameData;
        }

        // Game blurb
        $blurb = '';

        if ($gameData->category) {

            if ($gameData->category->blurb_option) {

                $categoryBlurb = $this->getServiceCategory()->parseBlurbOption($gameData->category);

            } else {

                $categoryBlurb = 'a game for the Nintendo Switch';

            }

            $blurb .= '<strong>'.$gameData->title.'</strong> is '.$categoryBlurb.'. ';

        } elseif ($gameData->eu_is_released == 1) {

            $blurb .= '<strong>'.$gameData->title.'</strong> is currently uncategorised. (Help us out!) ';

        } else {

            $blurb .= '<strong>'.$gameData->title.'</strong> is an upcoming game for the Nintendo Switch. ';

        }

        if ($gameData->game_rank) {

            $blurb .= 'It is ranked #'.$gameData->game_rank.' on the all-time Top Rated Switch games, '.
                'with a total of '.$gameData->review_count.' reviews. It has an average rating of '.$gameData->rating_avg.'.';

        } elseif ($gameData->eu_is_released == 1) {

            // If the game has no reviews but isn't released, this part can be ignored
            switch ($gameData->review_count) {
                case 0:
                    $blurb .= 'As it has no reviews, it is currently unranked. We need 3 reviews to give the game a rank. ';
                    break;
                case 1:
                    $blurb .= 'As it only has 1 review, it is currently unranked. We need 2 more reviews to give the game a rank. ';
                    break;
                case 2:
                    $blurb .= 'As it only has 2 reviews, it is currently unranked. We need 1 more review to give the game a rank. ';
                    break;
                default:
                    break;
            }

        }

        $bindings['GameBlurb'] = $blurb;
        $bindings['OgDescription'] = $blurb;

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
