<?php

namespace App\Http\Controllers\Staff\DataSources;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;
use App\Traits\StaffView;

use App\Services\DataSources\Queries\Differences;

class DashboardController extends Controller
{
    use SwitchServices;
    use StaffView;

    public function show()
    {
        $bindings = $this->getBindingsDashboardGenericSubpage('Data sources dashboard');

        $bindings['DataSources'] = $this->getServiceDataSource()->getAll();

        // Differences
        $dsDifferences = new Differences();
        $dsDifferences->setCountOnly(true);
        $releaseDateEUNintendoCoUkDifferenceCount = $dsDifferences->getReleaseDateEUNintendoCoUk();
        $priceNintendoCoUkDifferenceCount = $dsDifferences->getPriceNintendoCoUk();
        $playersEUNintendoCoUkDifferenceCount = $dsDifferences->getPlayersNintendoCoUk();
        $releaseDateEUWikipediaDifferenceCount = $dsDifferences->getReleaseDateEUWikipedia();
        $releaseDateUSWikipediaDifferenceCount = $dsDifferences->getReleaseDateUSWikipedia();
        $releaseDateJPWikipediaDifferenceCount = $dsDifferences->getReleaseDateJPWikipedia();

        $bindings['ReleaseDateEUNintendoCoUkDifferenceCount'] = $releaseDateEUNintendoCoUkDifferenceCount[0]->count;
        $bindings['PriceNintendoCoUkDifferenceCount'] = $priceNintendoCoUkDifferenceCount[0]->count;
        $bindings['PlayersNintendoCoUkDifferenceCount'] = $playersEUNintendoCoUkDifferenceCount[0]->count;
        $bindings['ReleaseDateEUWikipediaDifferenceCount'] = $releaseDateEUWikipediaDifferenceCount[0]->count;
        $bindings['ReleaseDateUSWikipediaDifferenceCount'] = $releaseDateUSWikipediaDifferenceCount[0]->count;
        $bindings['ReleaseDateJPWikipediaDifferenceCount'] = $releaseDateJPWikipediaDifferenceCount[0]->count;

        // We don't have a good way to count the records for devs/pubs
        $dsDifferences = new Differences();
        $nintendoCoUkPublishers = $dsDifferences->getPublishersNintendoCoUk();
        $wikipediaDevelopers = $dsDifferences->getDevelopersWikipedia();
        $wikipediaPublishers = $dsDifferences->getPublishersWikipedia();
        $nintendoCoUkGenres = $dsDifferences->getGenresNintendoCoUk();
        $wikipediaGenres = $dsDifferences->getGenresWikipedia();
        $bindings['PublishersNintendoCoUkDifferenceCount'] = count($nintendoCoUkPublishers);
        $bindings['DevelopersWikipediaDifferenceCount'] = count($wikipediaDevelopers);
        $bindings['PublishersWikipediaDifferenceCount'] = count($wikipediaPublishers);
        $bindings['GenresNintendoCoUkDifferenceCount'] = count($nintendoCoUkGenres);
        $bindings['GenresWikipediaDifferenceCount'] = count($wikipediaGenres);

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
