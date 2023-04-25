<?php

namespace App\Http\Controllers\PublicSite;

use App\Domain\GameDeveloper\DbQueries as GameDeveloperDbQueries;
use App\Domain\GamePublisher\DbQueries as GamePublisherDbQueries;
use App\Domain\GamesCompany\Repository as GamesCompanyRepository;
use App\Domain\ViewBreadcrumbs\MainSite as Breadcrumbs;
use Illuminate\Routing\Controller as Controller;

class GamesCompaniesController extends Controller
{
    private $viewBreadcrumbs;
    private $repoGamesCompany;
    private $dbGameDeveloper;
    private $dbGamePublisher;

    public function __construct(
        Breadcrumbs $viewBreadcrumbs,
        GamesCompanyRepository $repoGamesCompany,
        GameDeveloperDbQueries $dbGameDeveloper,
        GamePublisherDbQueries $dbGamePublisher
    )
    {
        $this->viewBreadcrumbs = $viewBreadcrumbs;
        $this->repoGamesCompany = $repoGamesCompany;
        $this->dbGameDeveloper = $dbGameDeveloper;
        $this->dbGamePublisher = $dbGamePublisher;
    }

    public function landing()
    {
        $pageTitle = 'Games companies';

        $bindings = [];

        $bindings['crumbNav'] = $this->viewBreadcrumbs->partnersSubpage($pageTitle);

        $bindings['NewestAdditions'] = $this->repoGamesCompany->newestNormalQuality();
        $bindings['MostPublishedGames'] = $this->repoGamesCompany->mostPublishedGames();

        $bindings['TopTitle'] = $pageTitle;
        $bindings['PageTitle'] = $pageTitle;

        return view('public.partners.games-companies.landing', $bindings);
    }

    public function companyProfile($linkTitle)
    {
        $gamesCompany = $this->repoGamesCompany->getByLinkTitle($linkTitle);
        if (!$gamesCompany) abort(404);

        $pageTitle = $gamesCompany->name.' - Profile';

        $gamesCompanyId = $gamesCompany->id;

        $bindings = [];

        $bindings['crumbNav'] = $this->viewBreadcrumbs->partnersSubpage($pageTitle);

        // Ranked
        $rankedDev = $this->dbGameDeveloper->byDeveloperRanked($gamesCompanyId);
        $rankedPub = $this->dbGamePublisher->byPublisherRanked($gamesCompanyId);
        $rankedList = $this->repoGamesCompany->getMergedGameList($rankedDev, $rankedPub);
        $mergedRankedList = collect($rankedList)->sortBy('game_rank')->toArray();
        $bindings['RankedGameList'] = $mergedRankedList;

        // Unranked
        $unrankedDev = $this->dbGameDeveloper->byDeveloperUnranked($gamesCompanyId);
        $unrankedPub = $this->dbGamePublisher->byPublisherUnranked($gamesCompanyId);
        $unrankedList = $this->repoGamesCompany->getMergedGameList($unrankedDev, $unrankedPub);
        $mergedUnrankedList = collect($unrankedList)->sortBy('title')->toArray();
        $bindings['UnrankedGameList'] = $mergedUnrankedList;

        // De-listed
        $delistedDev = $this->dbGameDeveloper->byDeveloperDelisted($gamesCompanyId);
        $delistedPub = $this->dbGamePublisher->byPublisherDelisted($gamesCompanyId);
        $delistedList = $this->repoGamesCompany->getMergedGameList($delistedDev, $delistedPub);
        $mergedDelistedList = collect($delistedList)->sortBy('title')->toArray();
        $bindings['DelistedGameList'] = $mergedDelistedList;


        $bindings['TopTitle'] = $pageTitle;
        $bindings['PageTitle'] = $pageTitle;

        $bindings['PartnerData'] = $gamesCompany;
        $bindings['PartnerId'] = $gamesCompanyId;

        return view('public.partners.games-companies.companyProfile', $bindings);
    }
}
