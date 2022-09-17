<?php

namespace App\Http\Controllers\Staff\Partners;

use Illuminate\Routing\Controller as Controller;

use App\Domain\GamesCompany\Repository as GamesCompanyRepository;
use App\Domain\GamesCompany\Stats as GamesCompanyStats;
use App\Domain\GameDeveloper\DbQueries as GameDeveloperDbQueries;
use App\Domain\GamePublisher\DbQueries as GamePublisherDbQueries;

class DashboardController extends Controller
{
    private $repoGamesCompany;
    private $statsGamesCompany;
    private $dbGameDeveloper;
    private $dbGamePublisher;

    public function __construct(
        GamesCompanyRepository $repoGamesCompany,
        GamesCompanyStats $statsGamesCompany,
        GameDeveloperDbQueries $dbGameDeveloper,
        GamePublisherDbQueries $dbGamePublisher
    )
    {
        $this->repoGamesCompany = $repoGamesCompany;
        $this->statsGamesCompany = $statsGamesCompany;
        $this->dbGameDeveloper = $dbGameDeveloper;
        $this->dbGamePublisher = $dbGamePublisher;
    }

    public function show()
    {
        $pageTitle = 'Partners dashboard';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->topLevelPage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        // Action lists
        $bindings['DeveloperMissingCount'] = $this->dbGameDeveloper->countGamesWithNoDeveloper();
        $bindings['PublisherMissingCount'] = $this->dbGamePublisher->countGamesWithNoPublisher();

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
