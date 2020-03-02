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
        $bindings['RecentWithGoodRanks'] = $this->getServiceGameReleaseDate()->getRecentWithGoodRanks(7, 35, 18);
        $bindings['ReviewList'] = $this->getServiceReviewLink()->getLatestNaturalOrder(20);
        $bindings['NewReleases'] = $this->getServiceGameReleaseDate()->getReleased(20);
        $bindings['TopRatedAllTime'] = $this->getServiceTopRated()->getList(20);
        $bindings['TopRatedThisYear'] = $this->getServiceGameRankYear()->getList($thisYear, 20);

        // --- Featured
        // Oct 2019
        $idLittleTownHero = 2925;
        $idCatQuestII = 3073;
        // Oct 2019
        $idLuigisMansion = 2706;
        // Nov 2019
        $idDisneyTsumTsumFestival = 3174;
        $idNewSuperLuckysTale = 3138;
        // 15th Nov 2019
        $idSparklite = 3078;
        $idPokemonSword = 2536;
        // 20th Dec 2019
        $idShovelKnightKingOfCards = 3311;
        $idShovelKnightShowdown = 3334;
        $idSuperEpic = 3275;
        // 22nd Jan 2020
        $idTokyoMirageSessions = 2926;
        $idSuperCrushKO = 3370;
        $idToTheMoon = 3426;
        $id198X = 3443;

        $featuredIdList = [
            $idSuperCrushKO,
            $idToTheMoon,
            $id198X,
        ];
        $featuredGameList = $this->getServiceGameReleaseDate()->getByIdList($featuredIdList);
        $featuredGameId = rand(0, count($featuredIdList)-1);
        $featuredGamesForView = new Collection();
        $featuredGamesForView->push($featuredGameList[$featuredGameId]);
        $bindings['FeaturedGameList'] = $featuredGamesForView;

        // Quick stats
        $bindings['TotalReleasedGames'] = $this->getServiceGameReleaseDate()->countReleased();
        $bindings['TotalRanked'] = $this->getServiceGame()->countRanked();
        $bindings['TotalReviews'] = $this->getServiceReviewLink()->countActive();

        $bindings['TopTitle'] = 'Welcome';
        $bindings['PageTitle'] = 'Switch Scores - Homepage';

        return view('welcome', $bindings);
    }
}
