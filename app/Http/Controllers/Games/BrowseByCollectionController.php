<?php

namespace App\Http\Controllers\Games;

use Illuminate\Routing\Controller as Controller;

use App\Domain\GameLists\Repository as GameListsRepository;
use App\Domain\GameLists\DbQueries as GameListsDbQueries;
use App\Domain\GameCollection\Repository as GameCollectionRepository;
use App\Domain\ViewBreadcrumbs\MainSite as Breadcrumbs;

use App\Traits\SwitchServices;

class BrowseByCollectionController extends Controller
{
    use SwitchServices;

    protected $repoGameLists;
    protected $dbGameLists;
    protected $repoGameCollection;
    protected $viewBreadcrumbs;

    public function __construct(
        GameListsRepository $repoGameLists,
        GameListsDbQueries $dbGameLists,
        GameCollectionRepository $repoGameCollection,
        Breadcrumbs $viewBreadcrumbs
    )
    {
        $this->repoGameLists = $repoGameLists;
        $this->dbGameLists = $dbGameLists;
        $this->repoGameCollection = $repoGameCollection;
        $this->viewBreadcrumbs = $viewBreadcrumbs;
    }

    public function landing()
    {
        $bindings = [];

        $bindings['CollectionList'] = $this->repoGameCollection->getAll();

        $bindings['PageTitle'] = 'Browse Nintendo Switch games by collection';
        $bindings['TopTitle'] = 'Browse Nintendo Switch games by collection';
        $bindings['crumbNav'] = $this->viewBreadcrumbs->gamesSubpage('By collection');

        return view('games.browse.byCollectionLanding', $bindings);
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

        return view('games.browse.byCollectionPage', $bindings);
    }
}
