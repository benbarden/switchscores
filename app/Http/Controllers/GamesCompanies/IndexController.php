<?php

namespace App\Http\Controllers\GamesCompanies;

use Illuminate\Routing\Controller as Controller;

use App\Domain\GamesCompany\Repository as GamesCompanyRepository;
use App\Domain\ReviewSite\Repository as ReviewSiteRepository;
use App\Domain\GamePublisher\DbQueries as GamePublisherDbQueries;
use App\Domain\GameDeveloper\DbQueries as GameDeveloperDbQueries;

class IndexController extends Controller
{
    public function __construct(
        private GamesCompanyRepository $repoGamesCompany,
        private ReviewSiteRepository $repoReviewSite,
        private GamePublisherDbQueries $dbGamePublisher,
        private GameDeveloperDbQueries $dbGameDeveloper
    )
    {
    }

    public function show()
    {
        $bindings = [];

        $bindings['ReviewSitesWithContactDetails'] = $this->repoReviewSite->getActiveWithContactDetails();

        $currentUser = resolve('User/Repository')->currentUser();
        $partnerId = $currentUser->games_company_id;
        $partnerData = $currentUser->gamesCompany;

        $gameDevList = $this->dbGameDeveloper->getGamesByDeveloper($partnerId, false);
        $gamePubList = $this->dbGamePublisher->getGamesByPublisher($partnerId, false);

        $mergedGameList = $this->repoGamesCompany->getMergedGameList($gameDevList, $gamePubList);

        $mergedGameList = collect($mergedGameList)->sortBy('eu_release_date')->reverse()->toArray();

        $releasedGames = collect($mergedGameList)->where('eu_is_released', '=', '1')->toArray();
        $upcomingGames = collect($mergedGameList)->where('eu_is_released', '=', '0')->toArray();

        $rankedGames = collect($releasedGames)->where('review_count', '>=', '3')->toArray();
        $unrankedGames = collect($releasedGames)->where('review_count', '<', '3')->toArray();

        $bindings['MergedGameList'] = $mergedGameList;
        $bindings['ReleasedGames'] = $releasedGames;
        $bindings['UpcomingGames'] = $upcomingGames;
        $bindings['RankedGames'] = $rankedGames;
        $bindings['UnrankedGames'] = $unrankedGames;
        $bindings['PartnerData'] = $partnerData;

        $pageTitle = 'Games company dashboard';
        $bindings['TopTitle'] = $pageTitle;
        $bindings['PageTitle'] = $pageTitle;

        if (request()->action == 'newsignup') {
            $bindings['ShowNewSignup'] = true;
        }

        return view('games-companies.index', $bindings);
    }
}
