<?php

namespace App\Http\Controllers\Staff\GamesCompanies;

use App\Domain\Game\QualityFilter as GameQualityFilter;
use App\Domain\GamesCompany\Repository as GamesCompanyRepository;
use App\Domain\GamesCompany\Stats as GamesCompanyStats;
use App\Domain\PartnerOutreach\Repository as PartnerOutreachRepository;
use App\Factories\GamesCompanyFactory;
use App\Models\GamesCompany;
use App\Traits\SwitchServices;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as Controller;

class ListController extends Controller
{
    use SwitchServices;

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    private $gameQualityFilter;
    private $repoGamesCompany;
    private $statsGamesCompany;
    private $repoPartnerOutreach;

    /**
     * @var array
     */
    private $validationRules = [
        'name' => 'required',
        'link_title' => 'required',
    ];

    public function __construct(
        GameQualityFilter $gameQualityFilter,
        GamesCompanyRepository $repoGamesCompany,
        GamesCompanyStats $statsGamesCompany,
        PartnerOutreachRepository $repoPartnerOutreach
    )
    {
        $this->gameQualityFilter = $gameQualityFilter;
        $this->repoGamesCompany = $repoGamesCompany;
        $this->statsGamesCompany = $statsGamesCompany;
        $this->repoPartnerOutreach = $repoPartnerOutreach;
    }

    public function showList()
    {
        $pageTitle = 'Games companies';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->gamesCompaniesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['GamesCompanyList'] = $this->repoGamesCompany->getAll();

        return view('staff.games-companies.list', $bindings);
    }

    public function normalQuality()
    {
        $pageTitle = 'Games companies';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->gamesCompaniesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['GamesCompanyList'] = $this->repoGamesCompany->normalQuality();

        return view('staff.games-companies.list', $bindings);
    }

    public function lowQuality()
    {
        $pageTitle = 'Games companies';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->gamesCompaniesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['GamesCompanyList'] = $this->repoGamesCompany->lowQuality();

        return view('staff.games-companies.list', $bindings);
    }

    public function withoutTwitterIds()
    {
        $pageTitle = 'Games companies without Twitter IDs';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->gamesCompaniesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['GamesCompanyList'] = $this->statsGamesCompany->getWithoutTwitterIds();

        return view('staff.games-companies.list', $bindings);
    }

    public function withoutWebsiteUrls()
    {
        $pageTitle = 'Games companies without website URLs';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->gamesCompaniesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['GamesCompanyList'] = $this->statsGamesCompany->getWithoutWebsiteUrls();

        return view('staff.games-companies.list', $bindings);
    }

    public function duplicateTwitterIds()
    {
        $pageTitle = 'Games companies with duplicate Twitter IDs';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->gamesCompaniesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $duplicateTwitterIdsList = $this->statsGamesCompany->getDuplicateTwitterIds();
        if ($duplicateTwitterIdsList) {
            $idList = [];
            foreach ($duplicateTwitterIdsList as $duplicateTwitterId) {
                $idList[] = $duplicateTwitterId->twitter_id;
            }
            $bindings['GamesCompanyList'] = $this->statsGamesCompany->getWithTwitterIdList($idList);
        }

        return view('staff.games-companies.list', $bindings);
    }

    public function duplicateWebsiteUrls()
    {
        $pageTitle = 'Games companies with duplicate website URLs';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->gamesCompaniesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $duplicateWebsiteUrlsList = $this->statsGamesCompany->getDuplicateWebsiteUrls();
        if ($duplicateWebsiteUrlsList) {
            $idList = [];
            foreach ($duplicateWebsiteUrlsList as $duplicateWebsiteUrl) {
                $idList[] = $duplicateWebsiteUrl->website_url;
            }
            $bindings['GamesCompanyList'] = $this->statsGamesCompany->getWithWebsiteUrlList($idList);
        }

        return view('staff.games-companies.list', $bindings);
    }

    public function devsWithUnrankedGames()
    {
        $tableSort = "[ 1, 'asc'], [ 3, 'asc']";
        $pageTitle = 'Outreach targets: Developers with unranked games';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->gamesCompaniesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')
            ->setTableSort($tableSort)->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['GamesCompanyList'] = $this->repoGamesCompany->getDevelopersWithUnrankedGames();

        return view('staff.games-companies.list-unranked', $bindings);
    }

    public function pubsWithUnrankedGames()
    {
        $tableSort = "[ 1, 'asc'], [ 3, 'asc']";
        $pageTitle = 'Outreach targets: Publishers with unranked games';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->gamesCompaniesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')
            ->setTableSort($tableSort)->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['GamesCompanyList'] = $this->repoGamesCompany->getPublishersWithUnrankedGames();
        $bindings['jsInitialSort'] = "[ 1, 'asc'], [ 3, 'asc']";

        return view('staff.games-companies.list-unranked', $bindings);
    }

}
