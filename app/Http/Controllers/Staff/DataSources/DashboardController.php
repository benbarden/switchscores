<?php

namespace App\Http\Controllers\Staff\DataSources;

use Illuminate\Routing\Controller as Controller;

use App\Domain\DataSource\Repository as DataSourceRepository;
use App\Domain\DataSourceIgnore\Repository as DataSourceIgnoreRepository;
use App\Domain\DataSourceParsed\Repository as DataSourceParsedRepository;

use App\Services\DataSources\Queries\Differences;

class DashboardController extends Controller
{
    public function __construct(
        private DataSourceRepository $repoDataSource,
        private DataSourceIgnoreRepository $repoDataSourceIgnore,
        private DataSourceParsedRepository $repoDataSourceParsed
    ){
    }

    public function show()
    {
        $pageTitle = 'Data sources dashboard';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->topLevelPage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['DataSources'] = $this->repoDataSource->getAll();

        // Differences
        $dsDifferences = new Differences();
        $dsDifferences->setCountOnly(true);
        $releaseDateEUNintendoCoUkDifferenceCount = $dsDifferences->getReleaseDateEUNintendoCoUk();
        $priceNintendoCoUkDifferenceCount = $dsDifferences->getPriceNintendoCoUk();
        $playersEUNintendoCoUkDifferenceCount = $dsDifferences->getPlayersNintendoCoUk();

        $bindings['ReleaseDateEUNintendoCoUkDifferenceCount'] = $releaseDateEUNintendoCoUkDifferenceCount[0]->count;
        $bindings['PriceNintendoCoUkDifferenceCount'] = $priceNintendoCoUkDifferenceCount[0]->count;
        $bindings['PlayersNintendoCoUkDifferenceCount'] = $playersEUNintendoCoUkDifferenceCount[0]->count;

        // We don't have a good way to count the records for devs/pubs
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

        return view('staff.data-sources.dashboard', $bindings);
    }
}
