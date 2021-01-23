<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Support\Collection;

use App\Domain\FeaturedGame\Repository as FeaturedGameRepository;

use App\Traits\SwitchServices;

class WelcomeController extends Controller
{
    use SwitchServices;

    protected $repoFeaturedGames;

    public function __construct(FeaturedGameRepository $featuredGames)
    {
        $this->repoFeaturedGames = $featuredGames;
    }

    public function show()
    {
        $bindings = [];

        $thisYear = date('Y');
        $bindings['Year'] = $thisYear;
        $bindings['RecentWithGoodRanks'] = $this->getServiceGameReleaseDate()->getRecentWithGoodRanks(7, 35, 15);
        $bindings['HighlightsRecentlyRanked'] = $this->getServiceReviewLink()->getHighlightsRecentlyRanked();
        $bindings['TopRatedThisYear'] = $this->getServiceGameRankYear()->getList($thisYear, 10);

        // Get latest News post
        $bindings['LatestNewsPost'] = $this->getServiceNews()->getNewest();

        // Get featured game
        $todaysDate = new \DateTime('now');
        $todaysDateYmd = $todaysDate->format('Y-m-d');
        $featuredGame = $this->repoFeaturedGames->getActiveByDateOrRandom($todaysDateYmd);
        // Make it into a usable collection
        $fGameList = new Collection();
        $fGameModel = $this->getServiceGame()->find($featuredGame->game_id);
        $fGameModel->featured_game = $featuredGame;
        $fGameList->push($fGameModel);
        $bindings['FeaturedGameList'] = $fGameList;
        $bindings['FeaturedGameData'] = $featuredGame;

        // Latest quick reviews
        $bindings['QuickReviews'] = $this->getServiceQuickReview()->getLatestActive(5);

        // Quick stats
        $bindings['TotalReleasedGames'] = $this->getServiceGameReleaseDate()->countReleased();
        $bindings['TotalRanked'] = $this->getServiceGame()->countRanked();
        $bindings['TotalReviews'] = $this->getServiceReviewLink()->countActive();

        $bindings['TopTitle'] = 'Welcome';
        $bindings['PageTitle'] = 'Switch Scores - Homepage';

        return view('welcome', $bindings);
    }
}
