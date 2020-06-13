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
        $bindings['ReviewList'] = $this->getServiceReviewLink()->getLatestNaturalOrder(35);
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
        $idNewSuperLuckysTale = 3138;
        // 15th Nov 2019
        $idSparklite = 3078;
        $idPokemonSword = 2536;
        // 20th Dec 2019
        $idShovelKnightKingOfCards = 3311;
        $idShovelKnightShowdown = 3334;
        $idSuperEpic = 3275;
        // 22nd Jan 2020
        $idSuperCrushKO = 3370;
        $idToTheMoon = 3426;
        // 8th March 2020
        $idMurderByNumbers = 3557;
        $idMegaManZero = 3404;
        $idTwoPointHospital = 3555;
        $idRuneFactory4Special = 3501;
        */

        // Get latest News post
        $bindings['LatestNewsPost'] = $this->getServiceNews()->getNewest();

        // Quick stats
        $bindings['TotalReleasedGames'] = $this->getServiceGameReleaseDate()->countReleased();
        $bindings['TotalRanked'] = $this->getServiceGame()->countRanked();
        $bindings['TotalReviews'] = $this->getServiceReviewLink()->countActive();

        $bindings['TopTitle'] = 'Welcome';
        $bindings['PageTitle'] = 'Switch Scores - Homepage';

        return view('welcome', $bindings);
    }
}
