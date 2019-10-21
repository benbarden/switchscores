<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Support\Collection;

use App\Traits\WosServices;
use App\Traits\SiteRequestData;

class WelcomeController extends Controller
{
    use WosServices;
    use SiteRequestData;

    public function show()
    {
        $regionCode = $this->getRegionCode();

        $bindings = [];

        $thisYear = date('Y');
        $bindings['Year'] = $thisYear;
        $bindings['RecentWithGoodRanks'] = $this->getServiceGameReleaseDate()->getRecentWithGoodRanks($regionCode, 7, 42, 18);
        $bindings['ReviewList'] = $this->getServiceReviewLink()->getLatestNaturalOrder(20);
        $bindings['NewReleases'] = $this->getServiceGameReleaseDate()->getReleased($regionCode, 20);
        $bindings['TopRatedAllTime'] = $this->getServiceTopRated()->getList($regionCode, 20);
        $bindings['TopRatedThisYear'] = $this->getServiceGameRankYear()->getList($thisYear, 20);

        // Featured
        $idLittleTownHero = 2925;
        $idCatQuestII = 3073;
        $featuredIdList = [
            //$idLittleTownHero,
            $idCatQuestII,
        ];
        $featuredGameList = $this->getServiceGameReleaseDate()->getByIdList($featuredIdList, $regionCode);
        $featuredGameId = rand(0, 1);
        $featuredGamesForView = new Collection();
        $featuredGamesForView->push($featuredGameList[$featuredGameId]);
        $bindings['FeaturedGameList'] = $featuredGamesForView;

        // Quick stats
        $bindings['TotalReleasedGames'] = $this->getServiceGameReleaseDate()->countReleased($regionCode);
        $bindings['TotalRanked'] = $this->getServiceGameRankAllTime()->countRanked();
        $bindings['TotalReviews'] = $this->getServiceReviewLink()->countActive();

        $bindings['TopTitle'] = 'Welcome';
        $bindings['PageTitle'] = 'World of Switch - Homepage';

        return view('welcome', $bindings);
    }
}
