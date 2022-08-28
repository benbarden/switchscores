<?php

namespace App\Http\Controllers\Staff\DataSources;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;

use App\Services\DataSources\Queries\Differences;

class DashboardController extends Controller
{
    use SwitchServices;

    public function show()
    {
        $pageTitle = 'Data sources dashboard';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->topLevelPage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['DataSources'] = $this->getServiceDataSource()->getAll();

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
        $ignoreIdList = $this->getServiceDataSourceIgnore()->getNintendoCoUkLinkIdList();
        $unlinkedItemList = $this->getServiceDataSourceParsed()->getAllNintendoCoUkWithNoGameId($ignoreIdList);
        $bindings['NintendoCoUkUnlinkedCount'] = $unlinkedItemList->count();
        $ignoredItemList = $this->getServiceDataSourceParsed()->getAllNintendoCoUkInLinkIdList($ignoreIdList);
        $bindings['NintendoCoUkIgnoredCount'] = $ignoredItemList->count();

        return view('staff.data-sources.dashboard', $bindings);
    }
}
