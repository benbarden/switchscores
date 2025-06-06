<?php

namespace App\Http\Controllers\PublicSite\Games;

use App\Domain\Game\Repository as GameRepository;
use App\Domain\GameLists\Repository as GameListsRepository;
use App\Domain\TopRated\DbQueries as TopRatedDbQueries;
use App\Domain\ViewBreadcrumbs\MainSite as Breadcrumbs;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Support\Collection;

class LandingController extends Controller
{
    public function __construct(
        private GameRepository $repoGame,
        private GameListsRepository $repoGameLists,
        private TopRatedDbQueries $dbTopRated,
        private Breadcrumbs $viewBreadcrumbs
    )
    {
    }

    public function landing()
    {
        $bindings = [];

        $bindings['NewReleases'] = $this->repoGameLists->recentlyReleasedExceptLowQuality(1, 20);
        $bindings['UpcomingReleases'] = $this->repoGameLists->upcoming(1, 20);

        $bindings['RecentWithGoodRanks'] = $this->repoGameLists->recentWithGoodRanks(7, 35, 15);

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

        $bindings['TopTitle'] = 'Nintendo Switch games list';
        $bindings['PageTitle'] = 'Nintendo Switch games list';
        $bindings['crumbNav'] = $this->viewBreadcrumbs->topLevelPage('Games');

        return view('public.games.landing', $bindings);
    }
}
