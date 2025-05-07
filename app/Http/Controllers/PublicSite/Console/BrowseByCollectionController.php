<?php

namespace App\Http\Controllers\PublicSite\Console;

use App\Domain\GameCollection\Repository as GameCollectionRepository;
use App\Domain\GameCollection\DbQueries as GameCollectionDbQueries;
use App\Domain\GameLists\Repository as GameListsRepository;
use App\Domain\Category\Repository as CategoryRepository;
use App\Domain\GameSeries\Repository as GameSeriesRepository;
use App\Domain\ViewBreadcrumbs\MainSite as Breadcrumbs;

use App\Models\Console;

use Illuminate\Routing\Controller as Controller;

class BrowseByCollectionController extends Controller
{

    public function __construct(
        private GameListsRepository $repoGameLists,
        private GameCollectionRepository $repoGameCollection,
        private GameCollectionDbQueries $dbGameCollection,
        private CategoryRepository $repoCategory,
        private GameSeriesRepository $repoGameSeries,
        private Breadcrumbs $viewBreadcrumbs
    )
    {
    }

    public function landing(Console $console)
    {
        $bindings = [];

        $bindings['CollectionList'] = $this->repoGameCollection->getAll();

        $bindings['Console'] = $console;

        $bindings['PageTitle'] = 'Nintendo '.$console->name.' games list - By collection';
        $bindings['TopTitle'] = 'Nintendo '.$console->name.' games list - By collection';
        $bindings['crumbNav'] = $this->viewBreadcrumbs->consoleSubpage('By collection', $console);

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

        // Lists
        $bindings['RankedGameList'] = $this->repoGameCollection->rankedByCollection($consoleId, $collectionId);
        $bindings['UnrankedGameList'] = $this->repoGameCollection->unrankedByCollection($consoleId, $collectionId);
        $bindings['DelistedGameList'] = $this->repoGameCollection->delistedByCollection($consoleId, $collectionId);
        $bindings['LowQualityGameList'] = $this->repoGameCollection->lowQualityByCollection($consoleId, $collectionId);

        $bindings['GameList'] = $gameList;
        $bindings['Console'] = $console;

        $bindings['PageTitle'] = 'Nintendo '.$console->name.' games list - By collection: '.$collectionName;
        $bindings['TopTitle'] = 'Nintendo '.$console->name.' games list - By collection: '.$collectionName;
        $bindings['crumbNav'] = $this->viewBreadcrumbs->consoleCollectionSubpage($collectionName, $console);

        return view('public.console.by-collection.page', $bindings);
    }
/*
    public function pageCategory($urlCollection, $urlCategory)
    {
        $bindings = [];

        $gameCollection = $this->repoGameCollection->getByLinkTitle($urlCollection);
        if (!$gameCollection) abort(404);

        $category = $this->repoCategory->getByLinkTitle($urlCategory);
        if (!$category) abort(404);

        $collectionId = $gameCollection->id;
        $collectionName = $gameCollection->name;
        $bindings['GameCollection'] = $gameCollection;

        $gameList = $this->repoGameLists->byCollectionAndCategory($collectionId, $category->id);
        $bindings['GameList'] = $gameList;

        $bindings['PageTitle'] = 'Nintendo Switch games list - '.$collectionName.' - '.$category->name;
        $bindings['TopTitle'] = 'Nintendo Switch games list - '.$collectionName.' - '.$category->name;
        $bindings['PageDesc'] = count($gameList).' games in '.$collectionName.' - '.$category->name.'.';
        $bindings['crumbNav'] = $this->viewBreadcrumbs->gamesByCollectionSubpage($collectionName);

        return view('public.games.browse.collection.page', $bindings);
    }

    public function pageSeries($urlCollection, $urlGameSeries)
    {
        $bindings = [];

        $gameCollection = $this->repoGameCollection->getByLinkTitle($urlCollection);
        if (!$gameCollection) abort(404);

        $gameSeries = $this->repoGameSeries->getByLinkTitle($urlGameSeries);
        if (!$gameSeries) abort(404);

        $collectionId = $gameCollection->id;
        $collectionName = $gameCollection->name;
        $bindings['GameCollection'] = $gameCollection;

        $gameList = $this->repoGameLists->byCollectionAndSeries($collectionId, $gameSeries->id);
        $bindings['GameList'] = $gameList;

        $bindings['PageTitle'] = 'Nintendo Switch games list - '.$collectionName.' - '.$gameSeries->series;
        $bindings['TopTitle'] = 'Nintendo Switch games list - '.$collectionName.' - '.$gameSeries->series;
        $bindings['PageDesc'] = count($gameList).' games in '.$collectionName.' - '.$gameSeries->series.'.';
        $bindings['crumbNav'] = $this->viewBreadcrumbs->gamesByCollectionSubpage($collectionName);

        return view('public.games.browse.collection.page', $bindings);
    }
    */
}
