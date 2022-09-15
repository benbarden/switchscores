<?php

namespace App\Http\Controllers\GamesCompanies;

use Illuminate\Routing\Controller as Controller;

use App\Domain\GamesCompany\Repository as GamesCompanyRepository;
use App\Domain\ReviewSite\Repository as ReviewSiteRepository;

use App\Traits\SwitchServices;
use App\Traits\AuthUser;

class IndexController extends Controller
{
    use SwitchServices;
    use AuthUser;

    protected $repoGamesCompany;
    protected $repoReviewSite;

    public function __construct(
        GamesCompanyRepository $repoGamesCompany,
        ReviewSiteRepository $repoReviewSite
    )
    {
        $this->repoGamesCompany = $repoGamesCompany;
        $this->repoReviewSite = $repoReviewSite;
    }

    public function show()
    {
        $bindings = [];

        $bindings['ReviewSitesWithContactDetails'] = $this->repoReviewSite->getActiveWithContactDetails();

        $authUser = $this->getValidUser($this->getServiceUser());
        $partnerId = $authUser->games_company_id;
        $partnerData = $authUser->gamesCompany;

        $gameDevList = $this->getServiceGameDeveloper()->getGamesByDeveloper($partnerId, false);
        $gamePubList = $this->getServiceGamePublisher()->getGamesByPublisher($partnerId, false);

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
