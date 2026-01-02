<?php

namespace App\Http\Controllers\PublicSite\Console;

use Illuminate\Routing\Controller as Controller;

use App\Domain\View\Breadcrumbs\PublicBreadcrumbs;
use App\Domain\View\PageBuilders\PublicPageBuilder;

use App\Domain\GameCollection\Repository as GameCollectionRepository;
use App\Domain\GameLists\Repository as GameListsRepository;

use App\Models\Console;

class BrowseByCollectionController extends Controller
{
    public function __construct(
        private PublicPageBuilder $pageBuilder,
        private GameListsRepository $repoGameLists,
        private GameCollectionRepository $repoGameCollection,
    )
    {
    }

    public function landing(Console $console)
    {
        $pageTitle = 'Nintendo '.$console->name.' games list - By collection';
        $bindings = $this->pageBuilder->build($pageTitle, PublicBreadcrumbs::consoleSubpage('By collection', $console))->bindings;

        $bindings['CollectionList'] = $this->repoGameCollection->getAll();

        $bindings['Console'] = $console;

        return view('public.console.by-collection.landing', $bindings);
    }

    public function page(Console $console, $collection)
    {
        $bindings = [];

        $gameCollection = $this->repoGameCollection->getByLinkTitle($collection);
        if (!$gameCollection) abort(404);

        $consoleId = $console->id;
        $collectionId = $gameCollection->id;
        $collectionName = $gameCollection->name;
        $bindings['GameCollection'] = $gameCollection;

        $gameList = $this->repoGameLists->byCollection($consoleId, $collectionId);

        $pageTitle = 'Nintendo '.$console->name.' games list - By collection: '.$collectionName;
        $bindings = $this->pageBuilder->build($pageTitle, PublicBreadcrumbs::consoleCollectionSubpage($collectionName, $console))->bindings;

        // Lists
        $bindings['RankedGameList'] = $this->repoGameCollection->rankedByCollection($consoleId, $collectionId);
        $bindings['UnrankedGameList'] = $this->repoGameCollection->unrankedByCollection($consoleId, $collectionId);
        $bindings['DelistedGameList'] = $this->repoGameCollection->delistedByCollection($consoleId, $collectionId);
        $bindings['LowQualityGameList'] = $this->repoGameCollection->lowQualityByCollection($consoleId, $collectionId);

        $bindings['GameList'] = $gameList;
        $bindings['Console'] = $console;

        return view('public.console.by-collection.page', $bindings);
    }
}
