<?php

namespace App\Http\Controllers\Staff\Partners;

use Illuminate\Routing\Controller as Controller;

use App\Domain\ViewBreadcrumbs\Staff as Breadcrumbs;

use App\Domain\GamesCompany\Repository as GamesCompanyRepository;

use App\Traits\SwitchServices;
use App\Traits\StaffView;

class DashboardController extends Controller
{
    use SwitchServices;
    use StaffView;

    protected $viewBreadcrumbs;
    private $repoGamesCompany;

    public function __construct(
        Breadcrumbs $viewBreadcrumbs,
        GamesCompanyRepository $repoGamesCompany
    )
    {
        $this->viewBreadcrumbs = $viewBreadcrumbs;
        $this->repoGamesCompany = $repoGamesCompany;
    }

    public function show()
    {
        $bindings = $this->getBindings('Partners dashboard');

        $bindings['crumbNav'] = $this->viewBreadcrumbs->topLevelPage('Partners dashboard');

        // Action lists
        $bindings['DeveloperMissingCount'] = $this->getServiceGameDeveloper()->countGamesWithNoDeveloper();
        $bindings['PublisherMissingCount'] = $this->getServiceGamePublisher()->countGamesWithNoPublisher();

        $bindings['GamesCompaniesWithoutWebsiteUrlsCount'] = $this->getServicePartner()->countGamesCompaniesWithoutWebsiteUrls();
        $bindings['GamesCompaniesWithoutTwitterIdsCount'] = $this->getServicePartner()->countGamesCompaniesWithoutTwitterIds();

        $duplicateTwitterIdsList = $this->getServicePartner()->getGamesCompanyDuplicateTwitterIds();
        $duplicateWebsiteUrlsList = $this->getServicePartner()->getGamesCompanyDuplicateWebsiteUrls();
        $bindings['GamesCompanyDuplicateTwitterIdsCount'] = count($duplicateTwitterIdsList);
        $bindings['GamesCompanyDuplicateWebsiteUrlsCount'] = count($duplicateWebsiteUrlsList);

        // Outreach
        $devsWithUnrankedGames = $this->getServicePartner()->getDevelopersWithUnrankedGames();
        $bindings['DevsWithUnrankedGamesCount'] = count($devsWithUnrankedGames);
        $pubsWithUnrankedGames = $this->getServicePartner()->getPublishersWithUnrankedGames();
        $bindings['PubsWithUnrankedGamesCount'] = count($pubsWithUnrankedGames);

        // Low quality filter
        $bindings['GamesCompaniesNormalQualityCount'] = $this->repoGamesCompany->normalQualityCount();
        $bindings['GamesCompaniesLowQualityCount'] = $this->repoGamesCompany->lowQualityCount();

        return view('staff.partners.dashboard', $bindings);
    }
}
