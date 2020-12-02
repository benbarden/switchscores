<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Support\Collection;

use App\Traits\SwitchServices;

class WelcomeController extends Controller
{
    use SwitchServices;

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
