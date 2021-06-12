<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;
use App\Traits\AuthUser;

use App\Domain\FeaturedGame\Repository as FeaturedGameRepository;
use App\Domain\GameLists\Repository as GameListsRepository;
use App\Domain\GameStats\Repository as GameStatsRepository;
use App\Domain\ViewBreadcrumbs\MainSite as Breadcrumbs;

class GamesController extends Controller
{
    use SwitchServices;
    use AuthUser;

    protected $repoFeaturedGames;
    protected $repoGameLists;
    protected $repoGameStats;
    protected $viewBreadcrumbs;

    public function __construct(
        FeaturedGameRepository $featuredGames,
        GameListsRepository $repoGameLists,
        GameStatsRepository $repoGameStats,
        Breadcrumbs $viewBreadcrumbs
    )
    {
        $this->repoFeaturedGames = $featuredGames;
        $this->repoGameLists = $repoGameLists;
        $this->repoGameStats = $repoGameStats;
        $this->viewBreadcrumbs = $viewBreadcrumbs;
    }

    public function landing()
    {
        $bindings = [];

        $bindings['NewReleases'] = $this->repoGameLists->recentlyReleased(20);
        $bindings['UpcomingReleases'] = $this->repoGameLists->upcoming(30);

        $bindings['RecentWithGoodRanks'] = $this->repoGameLists->recentWithGoodRanks(7, 35, 15);
        $bindings['HighlightsRecentlyRanked'] = $this->getServiceReviewLink()->getHighlightsRecentlyRanked();
        $bindings['HighlightsStillUnranked'] = $this->getServiceReviewLink()->getHighlightsStillUnranked();
        $bindings['TopRatedDiscounts'] = $this->getServiceDataSourceParsed()->getGamesOnSaleGoodRanks(50);

        $bindings['CalendarThisMonth'] = date('Y-m');

        $bindings['TopTitle'] = 'Nintendo Switch games database';
        $bindings['PageTitle'] = 'Nintendo Switch games database';
        $bindings['crumbNav'] = $this->viewBreadcrumbs->topLevelPage('Games');

        return view('games.landing', $bindings);
    }

    public function recentReleases()
    {
        $bindings = [];

        $bindings['NewReleases'] = $this->repoGameLists->recentlyReleased(50);
        $bindings['CalendarThisMonth'] = date('Y-m');

        $bindings['TopTitle'] = 'Nintendo Switch recent releases';
        $bindings['PageTitle'] = 'Nintendo Switch recent releases';
        $bindings['crumbNav'] = $this->viewBreadcrumbs->gamesSubpage('Recent');

        return view('games.recentReleases', $bindings);
    }

    public function upcomingReleases()
    {
        $bindings = [];

        $bindings['UpcomingGames'] = $this->repoGameLists->upcoming();

        $bindings['TopTitle'] = 'Nintendo Switch upcoming games';
        $bindings['PageTitle'] = 'Upcoming Nintendo Switch games';
        $bindings['crumbNav'] = $this->viewBreadcrumbs->gamesSubpage('Upcoming');

        return view('games.upcomingReleases', $bindings);
    }

    public function gamesOnSale()
    {
        $bindings = [];

        $bindings['TopTitle'] = 'Nintendo Switch games currently on sale in Europe';
        $bindings['PageTitle'] = 'Nintendo Switch games currently on sale in Europe';
        $bindings['crumbNav'] = $this->viewBreadcrumbs->gamesSubpage('On sale');

        $bindings['GoodRanks'] = $this->getServiceDataSourceParsed()->getGamesOnSaleGoodRanks(50);
        $bindings['HighestDiscounts'] = $this->getServiceDataSourceParsed()->getGamesOnSaleHighestDiscounts(50);
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

        $bindings['crumbNav'] = $this->viewBreadcrumbs->gamesSubpage($gameData->title);

        // Main data
        $bindings['TopTitle'] = $gameData->title.' Nintendo Switch reviews';
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

        // Related games
        if ($gameData->category_id) {
            $bindings['CategoryName'] = $gameData->category->name;
            $bindings['RelatedByCategory'] = $this->repoGameLists->relatedByCategory($gameData->category_id, $gameId, 6);
        }
        if ($gameData->series_id) {
            $bindings['SeriesName'] = $gameData->series->series;
            $bindings['RelatedBySeries'] = $this->repoGameLists->relatedBySeries($gameData->series_id, $gameId, 6);
        }
        if ($gameData->collection_id) {
            $bindings['CollectionName'] = $gameData->gameCollection->name;
            $bindings['RelatedByCollection'] = $this->repoGameLists->relatedByCollection($gameData->collection_id, $gameId, 6);
        }

        // Total rank count
        $rankMaximum = $this->repoGameStats->totalRanked();
        $bindings['RankMaximum'] = $rankMaximum;

        // Top %
        if ($gameData->game_rank && $rankMaximum) {
            $topPercent = ($gameData->game_rank / $rankMaximum) * 100;
            if ($topPercent <= 50) {
                $bindings['TopPercent'] = round($topPercent, 0);
            }
        }

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

            if ($gameData->isDigitalDelisted()) {

                $blurb .= '<strong>' . $gameData->title . '</strong> is ' . $categoryBlurb .
                    '. It has been <strong>de-listed</strong> from the eShop. ';

            } else {

                $blurb .= '<strong>' . $gameData->title . '</strong> is ' . $categoryBlurb . '. ';

            }

        } elseif ($gameData->isDigitalDelisted()) {

            $blurb .= '<strong>'.$gameData->title.'</strong> is a game for the Nintendo Switch' .
                '. It has been <strong>de-listed</strong> from the eShop. ';

        } elseif ($gameData->eu_is_released == 1) {

            $blurb .= '<strong>'.$gameData->title.'</strong> is currently uncategorised. (Help us out!) ';

        } else {

            $blurb .= '<strong>'.$gameData->title.'</strong> is an upcoming game for the Nintendo Switch. ';

        }

        if ($gameData->isDigitalDelisted()) {

            $blurb .= 'De-listed games are no longer included in our site rankings.';

        } else {

            if ($gameData->game_rank) {

                $blurb .= 'It is ranked #'.$gameData->game_rank.' on the all-time Top Rated Switch games, '.
                    'with a total of '.$gameData->review_count.' reviews and an average score of '.$gameData->rating_avg.'. ';

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

        }

        if ($gameData->series_id) {

            $blurb .= 'It is part of the '.$gameData->series->series.' series. ';

        }

        if ($gameData->collection_id) {

            $blurb .= 'It is part of the '.$gameData->gameCollection->name.' collection. ';

        }

        $bindings['GameBlurb'] = $blurb;
        $bindings['MetaDescription'] = strip_tags($blurb);

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
