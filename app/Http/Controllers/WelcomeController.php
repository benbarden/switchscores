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

        $thisYear = date('Y');
        $bindings['Year'] = $thisYear;

        $recentTopRatedLimit = 30;
        $bindings['RecentTopRatedLimit'] = $recentTopRatedLimit;
        $bindings['RecentWithGoodRanks'] = $this->repoGameLists->recentWithGoodRanks(7, $recentTopRatedLimit, 15);

        $bindings['ReviewList'] = $this->getServiceReviewLink()->getLatestNaturalOrder(30);
        $bindings['TopRatedThisYear'] = $this->getServiceGameRankYear()->getList($thisYear, 10);

        // Get latest News post
        $bindings['LatestNewsPost'] = $this->getServiceNews()->getNewest();

        $bindings['TopTitle'] = 'Welcome';
        $bindings['PageTitle'] = 'Switch Scores - Homepage';

        return view('welcome', $bindings);
    }
}
