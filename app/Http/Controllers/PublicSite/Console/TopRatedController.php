<?php

namespace App\Http\Controllers\PublicSite\Console;

use Illuminate\Routing\Controller as Controller;

use App\Domain\TopRated\Repository as TopRatedRepository;
use App\Domain\ViewBreadcrumbs\MainSite as Breadcrumbs;
use App\Domain\GameCalendar\AllowedDates as GameCalendarAllowedDates;
use App\Models\Console;
use App\Domain\Console\Repository as ConsoleRepository;
use App\Domain\AffiliateCodes\Amazon as AmazonAffiliate;

class TopRatedController extends Controller
{
    public function __construct(
        private TopRatedRepository $repoTopRated,
        private Breadcrumbs $viewBreadcrumbs,
        private GameCalendarAllowedDates $allowedDates,
        private ConsoleRepository $repoConsole,
        private AmazonAffiliate $affiliateAmazon,
    )
    {
    }

    public function landing(Console $console)
    {
        $consoleName = $console->name;
        $consoleId = $console->id;
        $consoleList = $this->repoConsole->getAll();

        $bindings = [];
        $bindings['ConsoleName'] = $consoleName;
        $bindings['ConsoleId'] = $consoleId;
        $bindings['Console'] = $console;
        $bindings['ConsoleList'] = $consoleList;

        $thisYear = date('Y');
        $lastYear = $thisYear - 1;
        $bindings['Year'] = $thisYear;
        $bindings['LastYear'] = $lastYear;
        // Affiliates
        $topRatedThisYear = $this->repoTopRated->byConsoleAndYear($consoleId, $thisYear, 15);
        foreach ($topRatedThisYear as &$game) {
            $amazon = $this->affiliateAmazon->buildLinksForGame($game);
            $game['Amazon'] = $amazon;
        }
        $bindings['TopRatedThisYear'] = $topRatedThisYear;
        // Affiliates
        $topRatedAllTime = $this->repoTopRated->getListByConsole($consoleId, 1, 15);
        foreach ($topRatedAllTime as &$game) {
            $amazon = $this->affiliateAmazon->buildLinksForGame($game);
            $game['Amazon'] = $amazon;
        }
        $bindings['TopRatedAllTime'] = $topRatedAllTime;

        $bindings['ConsoleSwitch1'] = $this->repoConsole->find(Console::ID_SWITCH_1);
        $bindings['ConsoleSwitch2'] = $this->repoConsole->find(Console::ID_SWITCH_2);
        $bindings['Switch1Years'] = $this->allowedDates->releaseYearsByConsole(Console::ID_SWITCH_1);
        $bindings['Switch2Years'] = $this->allowedDates->releaseYearsByConsole(Console::ID_SWITCH_2);

        $bindings['TopTitle'] = 'Best Nintendo '.$consoleName.' games';
        $bindings['PageTitle'] = 'Best Nintendo '.$consoleName.' games';
        $bindings['crumbNav'] = $this->viewBreadcrumbs->topLevelPage('Top Rated');

        return view('public.console.top-rated.landing', $bindings);
    }

    public function allTime(Console $console)
    {
        $consoleName = $console->name;
        $consoleId = $console->id;
        $consoleList = $this->repoConsole->getAll();

        $bindings = [];
        $bindings['ConsoleName'] = $consoleName;
        $bindings['ConsoleId'] = $consoleId;
        $bindings['Console'] = $console;
        $bindings['ConsoleList'] = $consoleList;
        $bindings['CurrentPage'] = 1; // default

        $gamesList = $this->repoTopRated->getListByConsole($consoleId, 1, 100);
        // Affiliates
        foreach ($gamesList as &$game) {
            $amazon = $this->affiliateAmazon->buildLinksForGame($game);
            $game['Amazon'] = $amazon;
        }

        $bindings['TopRatedAllTime'] = $gamesList;

        $bindings['TopTitle'] = 'Best Nintendo '.$consoleName.' games of all time';
        $bindings['PageTitle'] = 'Best Nintendo '.$consoleName.' games of all time';
        $bindings['crumbNav'] = $this->viewBreadcrumbs->topRatedSubpage('All-time');

        return view('public.console.top-rated.allTime', $bindings);
    }

    public function allTimePage(Console $console, $page)
    {
        $consoleName = $console->name;
        $consoleId = $console->id;
        $consoleList = $this->repoConsole->getAll();

        $page = (int) $page;
        if (!$page) abort(404);
        if ($page > 5) abort(404);
        //if ($page == 'favicon.ico') abort(404);

        if ($page) {
            $maxRank = $page * 100;
            $minRank = $maxRank - 99;
        } else {
            $minRank = 1;
            $maxRank = 100;
        }

        $gamesList = $this->repoTopRated->getListByConsole($consoleId, $minRank, $maxRank);
        // Affiliates
        foreach ($gamesList as &$game) {
            $amazon = $this->affiliateAmazon->buildLinksForGame($game);
            $game['Amazon'] = $amazon;
        }

        $bindings = [];
        $bindings['ConsoleName'] = $consoleName;
        $bindings['ConsoleId'] = $consoleId;
        $bindings['Console'] = $console;
        $bindings['ConsoleList'] = $consoleList;
        $bindings['CurrentPage'] = $page;

        $bindings['TopRatedAllTime'] = $gamesList;

        $bindings['TopTitle'] = 'Best Nintendo '.$consoleName.' games of all time - Page '.$page;
        $bindings['PageTitle'] = 'Best Nintendo '.$consoleName.' games of all time - Page '.$page;
        $bindings['crumbNav'] = $this->viewBreadcrumbs->topRatedSubpage('All-time');

        return view('public.console.top-rated.allTime', $bindings);
    }

    public function byYear(Console $console, $year)
    {
        $consoleName = $console->name;
        $consoleId = $console->id;
        $consoleList = $this->repoConsole->getAll();

        $allowedYears = $this->allowedDates->releaseYearsByConsole($consoleId, false);
        if (!in_array($year, $allowedYears)) {
            abort(404);
        }

        $bindings = [];
        $bindings['ConsoleName'] = $consoleName;
        $bindings['ConsoleId'] = $consoleId;
        $bindings['Console'] = $console;
        $bindings['ConsoleList'] = $consoleList;

        $gamesList = $this->repoTopRated->byConsoleAndYear($consoleId, $year, 100);
        // Affiliates
        foreach ($gamesList as &$game) {
            $amazon = $this->affiliateAmazon->buildLinksForGame($game);
            $game['Amazon'] = $amazon;
        }

        $bindings['TopRatedByYear'] = $gamesList;
        $bindings['GamesTableSort'] = "[5, 'desc']";
        $bindings['Year'] = $year;

        $bindings['TopTitle'] = 'Best Nintendo '.$consoleName.' games of '.$year;
        $bindings['PageTitle'] = 'Best Nintendo '.$consoleName.' games of '.$year;
        $bindings['crumbNav'] = $this->viewBreadcrumbs->topRatedSubpage($year);

        return view('public.console.top-rated.byYear', $bindings);
    }

}
