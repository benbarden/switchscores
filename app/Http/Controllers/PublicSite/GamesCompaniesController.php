<?php

namespace App\Http\Controllers\PublicSite;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as Controller;

use App\Domain\View\Breadcrumbs\PublicBreadcrumbs;
use App\Domain\View\PageBuilders\PublicPageBuilder;

use App\Domain\GameDeveloper\DbQueries as GameDeveloperDbQueries;
use App\Domain\GamePublisher\DbQueries as GamePublisherDbQueries;
use App\Domain\GamesCompany\Repository as GamesCompanyRepository;
use App\Domain\GamesCompanySignup\Builder;
use App\Domain\GamesCompanySignup\Director;

class GamesCompaniesController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRules = [
        'contact_name' => 'required',
        'contact_role' => 'required',
        'contact_email' => 'required',
        'new_company_name' => 'required',
        'new_company_type' => 'required',
    ];

    public function __construct(
        private PublicPageBuilder $pageBuilder,
        private GamesCompanyRepository $repoGamesCompany,
        private GameDeveloperDbQueries $dbGameDeveloper,
        private GamePublisherDbQueries $dbGamePublisher
    )
    {
    }

    public function landing()
    {
        $pageTitle = 'Games companies';
        $bindings = $this->pageBuilder->build($pageTitle, PublicBreadcrumbs::partnersSubpage($pageTitle))->bindings;

        $bindings['NewestAdditions'] = $this->repoGamesCompany->newestNormalQuality();
        $bindings['MostPublishedGames'] = $this->repoGamesCompany->mostPublishedGames();

        return view('public.partners.games-companies.landing', $bindings);
    }

    public function companyProfile($linkTitle)
    {
        $gamesCompany = $this->repoGamesCompany->getByLinkTitle($linkTitle);
        if (!$gamesCompany) abort(404);

        $pageTitle = $gamesCompany->name.' - Profile';
        $bindings = $this->pageBuilder->build($pageTitle, PublicBreadcrumbs::partnersSubpage($pageTitle))->bindings;

        $gamesCompanyId = $gamesCompany->id;

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

        // Total
        $allDev = $this->dbGameDeveloper->getGamesByDeveloper($gamesCompanyId);
        $allPub = $this->dbGamePublisher->getGamesByPublisher($gamesCompanyId);
        $allGames = $this->repoGamesCompany->getMergedGameList($allDev, $allPub);
        $bindings['AllGames'] = $allGames;

        $bindings['TopTitle'] = $pageTitle;
        $bindings['PageTitle'] = $pageTitle;

        $bindings['PartnerData'] = $gamesCompany;
        $bindings['PartnerId'] = $gamesCompanyId;

        return view('public.partners.games-companies.companyProfile', $bindings);
    }

    public function signupPage()
    {
        $pageTitle = 'Games company signup';
        $bindings = $this->pageBuilder->build($pageTitle, PublicBreadcrumbs::partnersSubpage($pageTitle))->bindings;

        $request = request();

        if ($request->isMethod('post')) {

            $this->validate($request, $this->validationRules);

            $builder = new Builder();
            $director = new Director($builder);
            $director->buildNew($request->post());
            $director->save();

            return redirect(route('partners.games-companies.signupSuccess'));

        }

        return view('public.partners.games-companies.signupPage', $bindings);
    }
    public function signupSuccess()
    {
        $pageTitle = 'Games company signup - Thank you';
        $bindings = $this->pageBuilder->build($pageTitle, PublicBreadcrumbs::partnersSubpage($pageTitle))->bindings;

        return view('public.partners.games-companies.signupSuccess', $bindings);
    }
}
