<?php

namespace App\Http\Controllers\Staff\GamesCompanies;

use Illuminate\Routing\Controller as Controller;

use App\Domain\View\Breadcrumbs\StaffBreadcrumbs;
use App\Domain\View\PageBuilders\StaffPageBuilder;

use App\Domain\GameDeveloper\DbQueries as GameDeveloperDbQueries;
use App\Domain\GamePublisher\DbQueries as GamePublisherDbQueries;
use App\Domain\GamesCompany\Repository as GamesCompanyRepository;
use App\Domain\GamesCompany\Stats as GamesCompanyStats;

class DashboardController extends Controller
{
    public function __construct(
        private StaffPageBuilder $pageBuilder,
        private GamesCompanyRepository $repoGamesCompany,
        private GamesCompanyStats $statsGamesCompany,
        private GameDeveloperDbQueries $dbGameDeveloper,
        private GamePublisherDbQueries $dbGamePublisher
    )
    {
    }

    public function show()
    {
        $pageTitle = 'Games companies dashboard';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::gamesCompaniesDashboard())->bindings;

        // Action lists
        $bindings['DeveloperMissingCount'] = $this->dbGameDeveloper->countGamesWithNoDeveloper();
        $bindings['PublisherMissingCount'] = $this->dbGamePublisher->countGamesWithNoPublisher();

        $bindings['GamesCompaniesWithoutEmailsCount'] = $this->statsGamesCompany->countWithoutEmails();
        $bindings['GamesCompaniesWithoutWebsiteUrlsCount'] = $this->statsGamesCompany->countWithoutWebsiteUrls();
        $bindings['GamesCompaniesWithoutTwitterIdsCount'] = $this->statsGamesCompany->countWithoutTwitterIds();

        $duplicateTwitterIdsList = $this->statsGamesCompany->getDuplicateTwitterIds();
        $duplicateWebsiteUrlsList = $this->statsGamesCompany->getDuplicateWebsiteUrls();
        $bindings['GamesCompanyDuplicateTwitterIdsCount'] = count($duplicateTwitterIdsList);
        $bindings['GamesCompanyDuplicateWebsiteUrlsCount'] = count($duplicateWebsiteUrlsList);

        $allowedYears = resolve('Domain\GameCalendar\AllowedDates')->releaseYears();
        $bindings['AllowedYears'] = $allowedYears;

        // Outreach - Publishers
        $pubsWithUnrankedGamesAll = $this->repoGamesCompany->getPublishersWithUnrankedGames();
        $bindings['PubsWithUnrankedGamesCount'] = count($pubsWithUnrankedGamesAll);
        foreach ($allowedYears as $releaseYear) {
            ${'pubsWithUnrankedGames'.$releaseYear} = $this->repoGamesCompany->getPublishersWithUnrankedGames($releaseYear);
            $bindings['PubsWithUnrankedGamesCount'.$releaseYear] = count(${'pubsWithUnrankedGames'.$releaseYear});
        }

        // Low quality filter
        $bindings['GamesCompaniesNormalQualityCount'] = $this->repoGamesCompany->normalQualityCount();
        $bindings['GamesCompaniesLowQualityCount'] = $this->repoGamesCompany->lowQualityCount();

        return view('staff.games-companies.dashboard', $bindings);
    }
}
