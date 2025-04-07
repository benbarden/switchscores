<?php

namespace App\Http\Controllers\PublicSite;

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

        $thisYear = date('Y');
        $topRatedThisYear = $this->dbTopRated->byYear($thisYear, 10);
        if (count($topRatedThisYear) < 4) {
            $thisYear--;
            $topRatedThisYear = $this->dbTopRated->byYear($thisYear, 10);
        }
        $bindings['TopRatedThisYear'] = $topRatedThisYear;
        $bindings['Year'] = $thisYear;

        $bindings['TopTitle'] = 'Welcome';
        $bindings['PageTitle'] = 'Switch Scores - Homepage';

        return view('public.welcome', $bindings);
    }
}
