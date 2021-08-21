<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Support\Collection;

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
        $bindings['RecentWithGoodRanks'] = $this->repoGameLists->recentWithGoodRanks(7, 35, 14);
        $bindings['HighlightsRecentlyRanked'] = $this->getServiceReviewLink()->getHighlightsRecentlyRanked(14, 10);
        $bindings['TopRatedThisYear'] = $this->getServiceGameRankYear()->getList($thisYear, 10);

        // Get latest News post
        $bindings['LatestNewsPost'] = $this->getServiceNews()->getNewest();

        // Get featured game
        $todaysDate = new \DateTime('now');
        $todaysDateYmd = $todaysDate->format('Y-m-d');
        $featuredGame = $this->repoFeaturedGames->getActiveByDateOrRandom($todaysDateYmd);
        // Make it into a usable collection
        if ($featuredGame) {
            $fGameList = new Collection();
            $fGameModel = $this->getServiceGame()->find($featuredGame->game_id);
            $fGameModel->featured_game = $featuredGame;
            $fGameList->push($fGameModel);
            $bindings['FeaturedGameList'] = $fGameList;
            $bindings['FeaturedGameData'] = $featuredGame;
        }

        $bindings['TopTitle'] = 'Welcome';
        $bindings['PageTitle'] = 'Switch Scores - Homepage';

        return view('welcome', $bindings);
    }
}
