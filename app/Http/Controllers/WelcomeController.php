<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as Controller;

use App\Domain\FeaturedGame\Repository as FeaturedGameRepository;
use App\Domain\GameLists\Repository as GameListsRepository;

use App\Traits\SwitchServices;

class WelcomeController extends Controller
{
    use SwitchServices;

    protected $repoFeaturedGames;
    protected $repoGameLists;

    public function __construct(
        FeaturedGameRepository $featuredGames,
        GameListsRepository $repoGameLists
    )
    {
        $this->repoFeaturedGames = $featuredGames;
        $this->repoGameLists = $repoGameLists;
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

        // Get latest News post
        $bindings['LatestNewsPost'] = $this->getServiceNews()->getNewest();

        $bindings['TopTitle'] = 'Welcome';
        $bindings['PageTitle'] = 'Switch Scores - Homepage';

        return view('welcome', $bindings);
    }
}
