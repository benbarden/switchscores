<?php

namespace App\Http\Controllers\Staff\DataSources;

use Illuminate\Routing\Controller as Controller;

use App\Domain\View\Breadcrumbs\StaffBreadcrumbs;
use App\Domain\View\PageBuilders\StaffPageBuilder;
use App\Models\DataSource;

use App\Domain\DataSource\Repository as DataSourceRepository;
use App\Domain\DataSourceIgnore\Repository as DataSourceIgnoreRepository;
use App\Domain\DataSourceParsed\Repository as DataSourceParsedRepository;
use App\Domain\DataSourceImportRun\Repository as DataSourceImportRunRepository;
use App\Domain\DataSourceImportLog\Repository as DataSourceImportLogRepository;

use App\Services\DataSources\Queries\Differences;

class DashboardController extends Controller
{
    public function __construct(
        private StaffPageBuilder $pageBuilder,
        private DataSourceRepository $repoDataSource,
        private DataSourceIgnoreRepository $repoDataSourceIgnore,
        private DataSourceParsedRepository $repoDataSourceParsed,
        private DataSourceImportRunRepository $repoImportRun,
        private DataSourceImportLogRepository $repoImportLog
    ){
    }

    public function show()
    {
        $pageTitle = 'Data sources dashboard';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::dataSourcesDashboard())->bindings;

        // Import run history
        $nintendoCoUkSourceId = DataSource::DSID_NINTENDO_CO_UK;
        $recentRuns = $this->repoImportRun->getRecentDaysBySource($nintendoCoUkSourceId, 21);
        $runIds = $recentRuns->pluck('id')->toArray();
        $runCounts = $runIds ? $this->repoImportLog->getCountsByRunIds($runIds) : [];

        $bindings['RecentRuns'] = $recentRuns;
        $bindings['RunCounts'] = $runCounts;

        // Differences
        $dsDifferences = new Differences();
        $dsDifferences->setCountOnly(true);
        $releaseDateEUNintendoCoUkDifferenceCount = $dsDifferences->getReleaseDateEUNintendoCoUk();
        $priceNintendoCoUkDifferenceCount = $dsDifferences->getPriceNintendoCoUk();
        $playersEUNintendoCoUkDifferenceCount = $dsDifferences->getPlayersNintendoCoUk();

        $bindings['ReleaseDateEUNintendoCoUkDifferenceCount'] = $releaseDateEUNintendoCoUkDifferenceCount[0]->count;
        $bindings['PriceNintendoCoUkDifferenceCount'] = $priceNintendoCoUkDifferenceCount[0]->count;
        $bindings['PlayersNintendoCoUkDifferenceCount'] = $playersEUNintendoCoUkDifferenceCount[0]->count;

        $dsDifferences = new Differences();
        $nintendoCoUkPublishers = $dsDifferences->getPublishersNintendoCoUk();
        $nintendoCoUkGenres = $dsDifferences->getGenresNintendoCoUk();
        $bindings['PublishersNintendoCoUkDifferenceCount'] = count($nintendoCoUkPublishers);
        $bindings['GenresNintendoCoUkDifferenceCount'] = count($nintendoCoUkGenres);

        // Stats: Nintendo.co.uk
        $ignoreIdList = $this->repoDataSourceIgnore->getNintendoCoUkLinkIdList();
        $unlinkedItemList = $this->repoDataSourceParsed->getAllNintendoCoUkWithNoGameId($ignoreIdList);
        $bindings['NintendoCoUkUnlinkedCount'] = $unlinkedItemList->count();
        $ignoredItemList = $this->repoDataSourceParsed->getAllNintendoCoUkInLinkIdList($ignoreIdList);
        $bindings['NintendoCoUkIgnoredCount'] = $ignoredItemList->count();

        // Import totals: Nintendo.co.uk only
        $nintendoSource = $this->repoDataSource->find($nintendoCoUkSourceId);
        $bindings['NintendoSource'] = $nintendoSource;

        return view('staff.data-sources.dashboard', $bindings);
    }
}
