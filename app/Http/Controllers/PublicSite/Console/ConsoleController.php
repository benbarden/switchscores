<?php

namespace App\Http\Controllers\PublicSite\Console;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Support\Collection;

use App\Domain\View\Breadcrumbs\PublicBreadcrumbs;
use App\Domain\View\PageBuilders\PublicPageBuilder;

use App\Domain\Game\Repository as GameRepository;
use App\Domain\GameLists\Repository as GameListsRepository;
use App\Domain\TopRated\DbQueries as TopRatedDbQueries;
use App\Domain\GameCalendar\AllowedDates as GameCalendarAllowedDates;
use App\Models\Console;

class ConsoleController extends Controller
{
    public function __construct(
        private PublicPageBuilder $pageBuilder,
        private GameRepository $repoGame,
        private GameListsRepository $repoGameLists,
        private TopRatedDbQueries $dbTopRated,
        private GameCalendarAllowedDates $allowedDates,
    )
    {
    }

    public function landing(Console $console)
    {
        $consoleName = $console->name;
        $consoleId = $console->id;

        if (!$consoleId) abort(404);

        $pageTitle = 'Nintendo '.$consoleName.' games library';
        $bindings = $this->pageBuilder->build($pageTitle, PublicBreadcrumbs::console($console))->bindings;

        $bindings['NewReleases'] = $this->repoGameLists->recentlyReleasedExceptLowQuality($consoleId, 20);
        $bindings['UpcomingReleases'] = $this->repoGameLists->upcoming($consoleId, 20);

        $bindings['RecentWithGoodRanks'] = $this->repoGameLists->recentWithGoodRanksByConsole($consoleId, 7, 35, 15);

        $bindings['CalendarThisMonth'] = date('Y-m');

        // Get random game from Top 100
        $randomTop100Game = $this->dbTopRated->getRandomFromTop100ByConsole($consoleId);
        // Make it into a usable collection
        if ($randomTop100Game) {
            $topGameList = new Collection();
            $topGameModel = $this->repoGame->find($randomTop100Game->game_id);
            $topGameModel->GameList = $randomTop100Game;
            $topGameList->push($topGameModel);
            $bindings['RandomTop100Game'] = $topGameList;
        }

        // Meta!
        $consoleDesc = '';
        if ($consoleId == Console::ID_SWITCH_1) {
            $consoleDesc = Console::DESC_SWITCH_1;
            $launchDesc = 'the console launched in 2017';
        } elseif ($consoleId = Console::ID_SWITCH_2) {
            $consoleDesc = Console::DESC_SWITCH_2;
            $launchDesc = 'the console launched in 2025';
        }
        if ($consoleDesc) {
            $onPageDesc = sprintf('Browse every Nintendo %s title released since %s. This hub includes full review scores, rankings, release details, and expert/community reviews — helping you quickly find the best games to play next.', $consoleDesc, $launchDesc);
            $metaDesc = sprintf('Explore the full list of Nintendo %s games, complete with reviews, scores, and rankings. Filter by rating, release date, category, or popularity.', $consoleDesc, $launchDesc);
            $bindings['OnPageDesc'] = $onPageDesc;
            $bindings['MetaDescription'] = $metaDesc;
        }

        // List of years
        $bindings['AllowedYears'] = $this->allowedDates->releaseYearsByConsole($consoleId);

        $bindings['Console'] = $console;

        return view('public.console.landing', $bindings);
    }

    public function newReleases(Console $console)
    {
        $consoleName = $console->name;
        $consoleId = $console->id;

        $pageTitle = 'Nintendo '.$consoleName.' new releases';
        $bindings = $this->pageBuilder->build($pageTitle, PublicBreadcrumbs::consoleSubpage($pageTitle, $console))->bindings;

        $bindings['NewReleases'] = $this->repoGameLists->recentlyReleasedExceptLowQuality($consoleId, 50);
        $bindings['CalendarThisMonth'] = date('Y-m');

        $bindings['Console'] = $console;

        return view('public.lists.list-recent-releases', $bindings);
    }

    public function upcomingReleases(Console $console)
    {
        $consoleName = $console->name;
        $consoleId = $console->id;

        $pageTitle = 'Nintendo '.$consoleName.' upcoming games';
        $bindings = $this->pageBuilder->build($pageTitle, PublicBreadcrumbs::consoleSubpage($pageTitle, $console))->bindings;

        $bindings['UpcomingNext7Days'] = $this->repoGameLists->upcomingNextDays($consoleId, 7);
        $bindings['UpcomingNext14Days'] = $this->repoGameLists->upcomingBetweenDays($consoleId, 7, 14);
        $bindings['UpcomingNext28Days'] = $this->repoGameLists->upcomingBetweenDays($consoleId, 14, 28);
        $bindings['UpcomingBeyond28Days'] = $this->repoGameLists->upcomingBeyondDays($consoleId, 28);

        $bindings['Console'] = $console;

        return view('public.lists.list-upcoming-releases', $bindings);
    }
}