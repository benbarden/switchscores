<?php

namespace App\Http\Controllers\PublicSite\Console;

use App\Domain\Game\Repository as GameRepository;
use App\Domain\GameLists\Repository as GameListsRepository;
use App\Domain\TopRated\DbQueries as TopRatedDbQueries;
use App\Domain\ViewBreadcrumbs\MainSite as Breadcrumbs;
use App\Domain\GameCalendar\AllowedDates as GameCalendarAllowedDates;
use App\Models\Console;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Support\Collection;


class ConsoleController extends Controller
{
    public function __construct(
        private GameRepository $repoGame,
        private GameListsRepository $repoGameLists,
        private TopRatedDbQueries $dbTopRated,
        private Breadcrumbs $viewBreadcrumbs,
        private GameCalendarAllowedDates $allowedDates,
    )
    {
    }

    public function landing(Console $console)
    {
        $consoleName = $console->name;
        $consoleId = $console->id;

        $bindings = [];

        $bindings['NewReleases'] = $this->repoGameLists->recentlyReleasedExceptLowQuality($consoleId, 20);
        $bindings['UpcomingReleases'] = $this->repoGameLists->upcoming($consoleId, 20);

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

        // List of years
        $bindings['AllowedYears'] = $this->allowedDates->releaseYearsByConsole($consoleId);

        $bindings['TopTitle'] = 'Nintendo '.$consoleName.' games';
        $bindings['PageTitle'] = 'Nintendo '.$consoleName.' games';
        $bindings['crumbNav'] = $this->viewBreadcrumbs->topLevelPage($consoleName);

        $bindings['Console'] = $console;

        return view('public.console.landing', $bindings);
    }

    public function newReleases(Console $console)
    {
        $consoleName = $console->name;
        $consoleId = $console->id;

        $bindings = [];

        $bindings['NewReleases'] = $this->repoGameLists->recentlyReleasedExceptLowQuality($consoleId, 50);
        $bindings['CalendarThisMonth'] = date('Y-m');

        $bindings['TopTitle'] = 'Nintendo '.$consoleName.' new releases';
        $bindings['PageTitle'] = 'Nintendo '.$consoleName.' new releases';
        $bindings['crumbNav'] = $this->viewBreadcrumbs->listsSubpage('New releases');

        $bindings['Console'] = $console;

        return view('public.lists.list-recent-releases', $bindings);
    }

    public function upcomingReleases(Console $console)
    {
        $consoleName = $console->name;
        $consoleId = $console->id;

        $bindings = [];

        $bindings['UpcomingNext7Days'] = $this->repoGameLists->upcomingNextDays($consoleId, 7);
        $bindings['UpcomingNext14Days'] = $this->repoGameLists->upcomingBetweenDays($consoleId, 7, 14);
        $bindings['UpcomingNext28Days'] = $this->repoGameLists->upcomingBetweenDays($consoleId, 14, 28);
        $bindings['UpcomingBeyond28Days'] = $this->repoGameLists->upcomingBeyondDays($consoleId, 28);

        $bindings['TopTitle'] = 'Nintendo '.$consoleName.' upcoming games';
        $bindings['PageTitle'] = 'Upcoming Nintendo '.$consoleName.' games';
        $bindings['crumbNav'] = $this->viewBreadcrumbs->listsSubpage('Upcoming releases');

        $bindings['Console'] = $console;

        return view('public.lists.list-upcoming-releases', $bindings);
    }
}