<?php

namespace App\Http\Controllers\PublicSite\Games;

use App\Domain\FeaturedGame\Repository as FeaturedGameRepository;
use App\Domain\Game\Repository as GameRepository;
use App\Domain\GameLists\Repository as GameListsRepository;
use App\Domain\GameStats\Repository as GameStatsRepository;
use App\Domain\TopRated\DbQueries as TopRatedDbQueries;
use App\Domain\ViewBreadcrumbs\MainSite as Breadcrumbs;

use App\Traits\SwitchServices;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Support\Collection;

class LandingController extends Controller
{
    use SwitchServices;

    protected $repoFeaturedGames;
    protected $repoGame;
    protected $repoGameLists;
    protected $repoGameStats;
    protected $dbTopRated;
    protected $viewBreadcrumbs;

    public function __construct(
        FeaturedGameRepository $featuredGames,
        GameRepository $repoGame,
        GameListsRepository $repoGameLists,
        GameStatsRepository $repoGameStats,
        TopRatedDbQueries $dbTopRated,
        Breadcrumbs $viewBreadcrumbs
    )
    {
        $this->repoFeaturedGames = $featuredGames;
        $this->repoGame = $repoGame;
        $this->repoGameLists = $repoGameLists;
        $this->repoGameStats = $repoGameStats;
        $this->dbTopRated = $dbTopRated;
        $this->viewBreadcrumbs = $viewBreadcrumbs;
    }

    public function landing()
    {
        $bindings = [];

        $bindings['NewReleases'] = $this->repoGameLists->recentlyReleased(20);
        $bindings['UpcomingReleases'] = $this->repoGameLists->upcoming(20);

        $bindings['RecentWithGoodRanks'] = $this->repoGameLists->recentWithGoodRanks(7, 35, 15);
        $bindings['HighlightsRecentlyRanked'] = $this->getServiceReviewLink()->getHighlightsRecentlyRanked();
        $bindings['HighlightsStillUnranked'] = $this->getServiceReviewLink()->getHighlightsStillUnranked();
        $bindings['TopRatedDiscounts'] = $this->getServiceDataSourceParsed()->getGamesOnSaleGoodRanks(50);

        $bindings['CalendarThisMonth'] = date('Y-m');

        // Get random game from Top 100
        $randomTop100Game = $this->dbTopRated->getRandomFromTop100();
        // Make it into a usable collection
        if ($randomTop100Game) {
            $topGameList = new Collection();
            $topGameModel = $this->repoGame->find($randomTop100Game->game_id);
            $topGameModel->GameList = $randomTop100Game;
            $topGameList->push($topGameModel);
            $bindings['RandomTop100Game'] = $topGameList;
        }

        $bindings['TopTitle'] = 'Nintendo Switch games database';
        $bindings['PageTitle'] = 'Nintendo Switch games database';
        $bindings['crumbNav'] = $this->viewBreadcrumbs->topLevelPage('Games');

        return view('public.games.landing', $bindings);
    }
}
