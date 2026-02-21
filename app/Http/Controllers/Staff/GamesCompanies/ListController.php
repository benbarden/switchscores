<?php

namespace App\Http\Controllers\Staff\GamesCompanies;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as Controller;

use App\Domain\View\Breadcrumbs\StaffBreadcrumbs;
use App\Domain\View\PageBuilders\StaffPageBuilder;

use App\Domain\Game\QualityFilter as GameQualityFilter;
use App\Domain\GamesCompany\Repository as GamesCompanyRepository;
use App\Domain\GamesCompany\Stats as GamesCompanyStats;
use App\Domain\PartnerOutreach\Repository as PartnerOutreachRepository;

use Illuminate\Http\Request;

class ListController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRules = [
        'name' => 'required',
        'link_title' => 'required',
    ];

    public function __construct(
        private StaffPageBuilder $pageBuilder,
        private GameQualityFilter $gameQualityFilter,
        private GamesCompanyRepository $repoGamesCompany,
        private GamesCompanyStats $statsGamesCompany,
        private PartnerOutreachRepository $repoPartnerOutreach
    )
    {
    }

    private $searchValidationRules = [
        'search_keywords' => 'required|min:3',
    ];

    public function search(Request $request)
    {
        $pageTitle = 'Games company search';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::gamesCompaniesSubpage($pageTitle))->bindings;

        if ($request->isMethod('post')) {

            $this->validate($request, $this->searchValidationRules);

            $keywords = $request->search_keywords;

            if ($keywords) {
                $bindings['SearchKeywords'] = $keywords;
                $bindings['SearchResults'] = $this->repoGamesCompany->searchGamesCompany($keywords);
            }

        }

        return view('staff.games-companies.search', $bindings);
    }

    public function normalQuality()
    {
        $pageTitle = 'Games company list';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::gamesCompaniesSubpage($pageTitle))->bindings;

        $bindings['GamesCompanyList'] = $this->repoGamesCompany->normalQuality();

        return view('staff.games-companies.list', $bindings);
    }

    public function lowQuality()
    {
        $pageTitle = 'Games company list';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::gamesCompaniesSubpage($pageTitle))->bindings;

        $bindings['GamesCompanyList'] = $this->repoGamesCompany->lowQuality();

        return view('staff.games-companies.list', $bindings);
    }

    public function withoutEmails()
    {
        $pageTitle = 'Games companies without Emails';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::gamesCompaniesSubpage($pageTitle))->bindings;

        $bindings['GamesCompanyList'] = $this->statsGamesCompany->getWithoutEmails();

        return view('staff.games-companies.list', $bindings);
    }

    public function withoutTwitterIds()
    {
        $pageTitle = 'Games companies without Twitter IDs';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::gamesCompaniesSubpage($pageTitle))->bindings;

        $bindings['GamesCompanyList'] = $this->statsGamesCompany->getWithoutTwitterIds();

        return view('staff.games-companies.list', $bindings);
    }

    public function withoutWebsiteUrls()
    {
        $pageTitle = 'Games companies without website URLs';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::gamesCompaniesSubpage($pageTitle))->bindings;

        $bindings['GamesCompanyList'] = $this->statsGamesCompany->getWithoutWebsiteUrls();

        return view('staff.games-companies.list', $bindings);
    }

    public function duplicateTwitterIds()
    {
        $pageTitle = 'Games companies with duplicate Twitter IDs';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::gamesCompaniesSubpage($pageTitle))->bindings;

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
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::gamesCompaniesSubpage($pageTitle))->bindings;

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

    public function pubsWithUnrankedGames($releaseYear = null)
    {
        $tableSort = "[ 5, 'desc'], [ 6, 'desc']";
        $pageTitle = 'Outreach targets: Publishers with unranked games';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::gamesCompaniesSubpage($pageTitle), jsInitialSort: $tableSort)->bindings;

        $bindings['GamesCompanyList'] = $this->repoGamesCompany->getPublishersWithUnrankedGames($releaseYear);
        //$bindings['jsInitialSort'] = "[ 1, 'asc'], [ 3, 'asc']";

        return view('staff.games-companies.list-unranked', $bindings);
    }

}
