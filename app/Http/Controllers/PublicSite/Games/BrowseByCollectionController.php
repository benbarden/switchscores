<?php

namespace App\Http\Controllers\PublicSite\Games;

use App\Domain\GameCollection\Repository as GameCollectionRepository;
use App\Domain\GameCollection\DbQueries as GameCollectionDbQueries;
use App\Domain\GameLists\Repository as GameListsRepository;
use App\Domain\Category\Repository as CategoryRepository;
use App\Domain\GameSeries\Repository as GameSeriesRepository;
use App\Domain\ViewBreadcrumbs\MainSite as Breadcrumbs;

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

    public function landing()
    {
        $bindings = [];

        $bindings['CollectionList'] = $this->repoGameCollection->getAll();

        $bindings['PageTitle'] = 'Nintendo Switch games list - By collection';
        $bindings['TopTitle'] = 'Nintendo Switch games list - By collection';
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
        $bindings['GameCollection'] = $gameCollection;

        $gameList = $this->repoGameLists->byCollection($collectionId);

        $splitPageCutoff = 30;
        $useSplitPageCutoff = "Y"; // change to Y to test the new layout

        $bindings['GameList'] = $gameList;

        if (($useSplitPageCutoff == "Y") && (count($gameList) >= $splitPageCutoff)) {
            $gameCollectionCategoryList = $this->dbGameCollection->collectionCategoryStats($collectionId);
            $gameCollectionSeriesList = $this->dbGameCollection->collectionSeriesStats($collectionId);
            $bindings['CollectionCategoryList'] = $gameCollectionCategoryList;
            $bindings['CollectionSeriesList'] = $gameCollectionSeriesList;
        } else {
            $useSplitPageCutoff = "N"; // revert to old style if we don't have enough games
        }
        $bindings['UseSplitPageCutoff'] = $useSplitPageCutoff;

        $bindings['PageTitle'] = 'Nintendo Switch games list - By collection: '.$collectionName;
        $bindings['TopTitle'] = 'Nintendo Switch games list - By collection: '.$collectionName;
        $bindings['crumbNav'] = $this->viewBreadcrumbs->gamesByCollectionSubpage($collectionName);

        return view('public.games.browse.byCollectionPage', $bindings);
    }

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

        return view('public.games.browse.byCollectionPage', $bindings);
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

        return view('public.games.browse.byCollectionPage', $bindings);
    }
}
