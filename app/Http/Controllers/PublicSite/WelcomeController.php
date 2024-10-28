<?php

namespace App\Http\Controllers\PublicSite;

use App\Domain\FeaturedGame\Repository as FeaturedGameRepository;
use App\Domain\GameLists\Repository as GameListsRepository;
use App\Traits\SwitchServices;
use Illuminate\Routing\Controller as Controller;

class WelcomeController extends Controller
{
    use SwitchServices;

    public function __construct(
        private FeaturedGameRepository $repoFeaturedGames,
        private GameListsRepository $repoGameLists
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

        $bindings['ReviewList'] = $this->getServiceReviewLink()->getLatestNaturalOrder(30);

        $thisYear = date('Y');
        $topRatedThisYear = $this->getServiceGameRankYear()->getList($thisYear, 10);
        if (count($topRatedThisYear) < 4) {
            $thisYear--;
            $topRatedThisYear = $this->getServiceGameRankYear()->getList($thisYear, 10);
        }
        $bindings['TopRatedThisYear'] = $topRatedThisYear;
        $bindings['Year'] = $thisYear;

        $bindings['TopTitle'] = 'Welcome';
        $bindings['PageTitle'] = 'Switch Scores - Homepage';

        return view('public.welcome', $bindings);
    }
}
