<?php

namespace App\Http\Controllers\PublicSite;

use App\Models\Console;
use Illuminate\Routing\Controller as Controller;

use App\Domain\GameLists\Repository as GameListsRepository;
use App\Domain\TopRated\DbQueries as TopRatedDbQueries;
use App\Domain\ReviewLink\Repository as ReviewLinkRepository;

class WelcomeController extends Controller
{
    public function __construct(
        private GameListsRepository $repoGameLists,
        private TopRatedDbQueries $dbTopRated,
        private ReviewLinkRepository $repoReviewLink
    )
    {
    }

    public function show()
    {
        $bindings = [];

        $recentTopRatedLimit = 30;
        $recentWithGoodRanks = $this->repoGameLists->recentWithGoodRanks(7, $recentTopRatedLimit, 15);
        if (count($recentWithGoodRanks) < 4) {
            $recentTopRatedLimit = 45;
            $recentWithGoodRanks = $this->repoGameLists->recentWithGoodRanks(7, $recentTopRatedLimit, 15);
        }

        $bindings['RecentTopRatedLimit'] = $recentTopRatedLimit;
        $bindings['RecentWithGoodRanks'] = $recentWithGoodRanks;

        $bindings['ReviewList'] = $this->repoReviewLink->recentNaturalOrder(30);

        // Switch 1
        $thisYearSwitch1 = date('Y');
        $topRatedThisYearSwitch1 = $this->dbTopRated->byConsoleAndYear(Console::ID_SWITCH_1, $thisYearSwitch1, 8);
        if (count($topRatedThisYearSwitch1) < 4) {
            $thisYearSwitch1--;
            $topRatedThisYearSwitch1 = $this->dbTopRated->byConsoleAndYear(Console::ID_SWITCH_1, $thisYearSwitch1, 8);
        }
        $bindings['TopRatedThisYearSwitch1'] = $topRatedThisYearSwitch1;
        $bindings['YearSwitch1'] = $thisYearSwitch1;

        // Switch 2
        $thisYearSwitch2 = date('Y');
        $topRatedThisYearSwitch2 = $this->dbTopRated->byConsoleAndYear(Console::ID_SWITCH_2, $thisYearSwitch2, 8);
        if (count($topRatedThisYearSwitch2) < 4) {
            if ($thisYearSwitch2 > 2025) {
                $thisYearSwitch2--;
                $topRatedThisYearSwitch2 = $this->dbTopRated->byConsoleAndYear(Console::ID_SWITCH_2, $thisYearSwitch2, 8);
            }
        }
        $bindings['TopRatedThisYearSwitch2'] = $topRatedThisYearSwitch2;
        $bindings['YearSwitch2'] = $thisYearSwitch2;

        $bindings['TopTitle'] = 'Welcome';
        $bindings['PageTitle'] = 'Switch Scores - Homepage';

        return view('public.welcome', $bindings);
    }
}
