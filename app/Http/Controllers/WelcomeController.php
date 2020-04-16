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

        /*
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
        // 8th March 2020
        $idMurderByNumbers = 3557;
        $idMegaManZero = 3404;
        $idTwoPointHospital = 3555;
        $idRuneFactory4Special = 3501;
        */

        // Featured games from News page
        $featuredGameCategory = $this->getServiceNewsCategory()->getByUrl('featured-games');
        if ($featuredGameCategory) {
            $featuredGameCategoryId = $featuredGameCategory->id;
            $featuredGame = $this->getServiceNews()->getByCategory($featuredGameCategoryId, 1);
            if (count($featuredGame) > 0) {
                $bindings['FeaturedGame'] = $featuredGame[0];
            }
        }

        // Quick stats
        $bindings['TotalReleasedGames'] = $this->getServiceGameReleaseDate()->countReleased();
        $bindings['TotalRanked'] = $this->getServiceGame()->countRanked();
        $bindings['TotalReviews'] = $this->getServiceReviewLink()->countActive();

        $bindings['TopTitle'] = 'Welcome';
        $bindings['PageTitle'] = 'Switch Scores - Homepage';

        return view('welcome', $bindings);
    }
}
