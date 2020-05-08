<?php

namespace App\Http\Controllers\Staff\DataSources;

use App\Services\DataSources\Queries\Differences;
use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;

class DashboardController extends Controller
{
    use SwitchServices;

    public function show()
    {
        $pageTitle = 'Data sources dashboard';

        $bindings = [];

        $bindings['TopTitle'] = $pageTitle;
        $bindings['PageTitle'] = $pageTitle;

        $bindings['DataSources'] = $this->getServiceDataSource()->getAll();

        // Differences
        $dsDifferences = new Differences();

        $releaseDateEUNintendoCoUkDifferenceCount = $dsDifferences->getReleaseDateEUNintendoCoUk(true);
        $releaseDateEUWikipediaDifferenceCount = $dsDifferences->getReleaseDateEUWikipedia(true);
        $releaseDateUSWikipediaDifferenceCount = $dsDifferences->getReleaseDateUSWikipedia(true);
        $releaseDateJPWikipediaDifferenceCount = $dsDifferences->getReleaseDateJPWikipedia(true);

        $bindings['ReleaseDateEUNintendoCoUkDifferenceCount'] = $releaseDateEUNintendoCoUkDifferenceCount[0]->count;
        $bindings['ReleaseDateEUWikipediaDifferenceCount'] = $releaseDateEUWikipediaDifferenceCount[0]->count;
        $bindings['ReleaseDateUSWikipediaDifferenceCount'] = $releaseDateUSWikipediaDifferenceCount[0]->count;
        $bindings['ReleaseDateJPWikipediaDifferenceCount'] = $releaseDateJPWikipediaDifferenceCount[0]->count;

        // Stats: Nintendo.co.uk
        $ignoreIdList = $this->getServiceDataSourceIgnore()->getNintendoCoUkLinkIdList();
        $unlinkedItemList = $this->getServiceDataSourceParsed()->getAllNintendoCoUkWithNoGameId($ignoreIdList);
        $bindings['NintendoCoUkUnlinkedCount'] = $unlinkedItemList->count();
        $ignoredItemList = $this->getServiceDataSourceParsed()->getAllNintendoCoUkInLinkIdList($ignoreIdList);
        $bindings['NintendoCoUkIgnoredCount'] = $ignoredItemList->count();

        // Stats: Wikipedia
        $ignoreTitleList = $this->getServiceDataSourceIgnore()->getWikipediaTitleList();
        $unlinkedItemList = $this->getServiceDataSourceParsed()->getAllWikipediaWithNoGameId($ignoreTitleList);
        $bindings['WikipediaUnlinkedCount'] = $unlinkedItemList->count();
        $ignoredItemList = $this->getServiceDataSourceParsed()->getAllWikipediaInTitleList($ignoreTitleList);
        $bindings['WikipediaIgnoredCount'] = $ignoredItemList->count();

        return view('staff.data-sources.dashboard', $bindings);
    }
}
