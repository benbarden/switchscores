<?php

namespace App\Http\Controllers\PublicSite;

use App\Domain\GameDeveloper\DbQueries as GameDeveloperDbQueries;
use App\Domain\GamePublisher\DbQueries as GamePublisherDbQueries;
use App\Domain\GamesCompany\Repository as GamesCompanyRepository;
use App\Domain\GamesCompanySignup\Builder;
use App\Domain\GamesCompanySignup\Director;
use App\Domain\ViewBreadcrumbs\MainSite as Breadcrumbs;

use App\Domain\GamesCompanySignup\Director as GamesCompanySignupDirector;
use App\Domain\GamesCompanySignup\Builder as GamesCompanySignupBuilder;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as Controller;

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
        private Breadcrumbs $viewBreadcrumbs,
        private GamesCompanyRepository $repoGamesCompany,
        private GameDeveloperDbQueries $dbGameDeveloper,
        private GamePublisherDbQueries $dbGamePublisher
    )
    {
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

        $bindings = [];

        $bindings['crumbNav'] = $this->viewBreadcrumbs->partnersSubpage($pageTitle);

        $bindings['TopTitle'] = $pageTitle;
        $bindings['PageTitle'] = $pageTitle;

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

        $bindings = [];

        $bindings['crumbNav'] = $this->viewBreadcrumbs->partnersSubpage($pageTitle);

        $bindings['TopTitle'] = $pageTitle;
        $bindings['PageTitle'] = $pageTitle;

        return view('public.partners.games-companies.signupSuccess', $bindings);
    }
}
