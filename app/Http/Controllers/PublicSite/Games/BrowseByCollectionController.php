<?php

namespace App\Http\Controllers\PublicSite\Games;

use App\Domain\GameCollection\Repository as GameCollectionRepository;
use App\Domain\GameLists\DbQueries as GameListsDbQueries;
use App\Domain\GameLists\Repository as GameListsRepository;
use App\Domain\ViewBreadcrumbs\MainSite as Breadcrumbs;
use App\Traits\SwitchServices;
use Illuminate\Routing\Controller as Controller;

class BrowseByCollectionController extends Controller
{
    use SwitchServices;

    public function __construct(
        private GameListsRepository $repoGameLists,
        private GameListsDbQueries $dbGameLists,
        private GameCollectionRepository $repoGameCollection,
        private Breadcrumbs $viewBreadcrumbs
    )
    {
    }

    public function landing()
    {
        $bindings = [];

        $bindings['CollectionList'] = $this->repoGameCollection->getAll();

        $bindings['PageTitle'] = 'Browse Nintendo Switch games by collection';
        $bindings['TopTitle'] = 'Browse Nintendo Switch games by collection';
        $bindings['crumbNav'] = $this->viewBreadcrumbs->gamesSubpage('By collection');

        return view('public.games.browse.byCollectionLanding', $bindings);
    }

    public function page($collection)
    {
        $bindings = [];

        $gameCollection = $this->repoGameCollection->getByLinkTitle($collection);
        if (!$gameCollection) abort(404);

        $collectionId = $gameCollection->id;
        $collectionName = $gameCollection->name;

        $gameList = $this->repoGameLists->byCollection($collectionId);

        $bindings['GameList'] = $gameList;

        $bindings['PageTitle'] = 'Browse Nintendo Switch games by collection: '.$collectionName;
        $bindings['TopTitle'] = 'Browse Nintendo Switch games by collection: '.$collectionName;
        $bindings['crumbNav'] = $this->viewBreadcrumbs->gamesByCollectionSubpage($collectionName);

        return view('public.games.browse.byCollectionPage', $bindings);
    }
}
