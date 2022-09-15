<?php

namespace App\Http\Controllers\Staff\Partners;

use Illuminate\Routing\Controller as Controller;

use App\Domain\ViewBreadcrumbs\Staff as Breadcrumbs;

use App\Domain\GamesCompany\Repository as GamesCompanyRepository;
use App\Domain\GamesCompany\Stats as GamesCompanyStats;

use App\Traits\SwitchServices;
use App\Traits\StaffView;

class DashboardController extends Controller
{
    use SwitchServices;
    use StaffView;

    private $viewBreadcrumbs;
    private $repoGamesCompany;
    private $statsGamesCompany;

    public function __construct(
        Breadcrumbs $viewBreadcrumbs,
        GamesCompanyRepository $repoGamesCompany,
        GamesCompanyStats $statsGamesCompany
    )
    {
        $this->viewBreadcrumbs = $viewBreadcrumbs;
        $this->repoGamesCompany = $repoGamesCompany;
        $this->statsGamesCompany = $statsGamesCompany;
    }

    public function show()
    {
        $bindings = $this->getBindings('Partners dashboard');

        $bindings['crumbNav'] = $this->viewBreadcrumbs->topLevelPage('Partners dashboard');

        // Action lists
        $bindings['DeveloperMissingCount'] = $this->getServiceGameDeveloper()->countGamesWithNoDeveloper();
        $bindings['PublisherMissingCount'] = $this->getServiceGamePublisher()->countGamesWithNoPublisher();

        $bindings['GamesCompaniesWithoutWebsiteUrlsCount'] = $this->statsGamesCompany->countWithoutWebsiteUrls();
        $bindings['GamesCompaniesWithoutTwitterIdsCount'] = $this->statsGamesCompany->countWithoutTwitterIds();

        $duplicateTwitterIdsList = $this->statsGamesCompany->getDuplicateTwitterIds();
        $duplicateWebsiteUrlsList = $this->statsGamesCompany->getDuplicateWebsiteUrls();
        $bindings['GamesCompanyDuplicateTwitterIdsCount'] = count($duplicateTwitterIdsList);
        $bindings['GamesCompanyDuplicateWebsiteUrlsCount'] = count($duplicateWebsiteUrlsList);

        // Outreach
        $devsWithUnrankedGames = $this->repoGamesCompany->getDevelopersWithUnrankedGames();
        $bindings['DevsWithUnrankedGamesCount'] = count($devsWithUnrankedGames);
        $pubsWithUnrankedGames = $this->repoGamesCompany->getPublishersWithUnrankedGames();
        $bindings['PubsWithUnrankedGamesCount'] = count($pubsWithUnrankedGames);

        // Low quality filter
        $bindings['GamesCompaniesNormalQualityCount'] = $this->repoGamesCompany->normalQualityCount();
        $bindings['GamesCompaniesLowQualityCount'] = $this->repoGamesCompany->lowQualityCount();

        return view('staff.partners.dashboard', $bindings);
    }
}
