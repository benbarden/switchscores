<?php

namespace App\Http\Controllers\Games;

use Illuminate\Routing\Controller as Controller;

use App\Domain\GameLists\Repository as GameListsRepository;
use App\Domain\GameLists\DbQueries as GameListsDbQueries;
use App\Domain\ViewBreadcrumbs\MainSite as Breadcrumbs;

use App\Traits\SwitchServices;

class BrowseByCategoryController extends Controller
{
    use SwitchServices;

    protected $repoGameLists;
    protected $viewBreadcrumbs;

    public function __construct(
        GameListsRepository $repoGameLists,
        GameListsDbQueries $dbGameLists,
        Breadcrumbs $viewBreadcrumbs
    )
    {
        $this->repoGameLists = $repoGameLists;
        $this->viewBreadcrumbs = $viewBreadcrumbs;
    }

    public function landing()
    {
        $bindings = [];

        $bindings['CategoryList'] = $this->getServiceCategory()->getAllWithoutParents();

        $bindings['PageTitle'] = 'Browse Nintendo Switch games by category';
        $bindings['TopTitle'] = 'Browse Nintendo Switch games by category';
        $bindings['crumbNav'] = $this->viewBreadcrumbs->gamesSubpage('By category');

        return view('games.browse.byCategoryLanding', $bindings);
    }

    public function page($category)
    {
        $bindings = [];

        $category = $this->getServiceCategory()->getByLinkTitle($category);
        if (!$category) abort(404);

        $categoryId = $category->id;
        $categoryName = $category->name;

        $bindings['Category'] = $category;

        // All games in category
        //$bindings['CategoryGameList'] = $this->repoGameLists->byCategory($categoryId);

        // Lists
        $bindings['RankedGameList'] = $this->repoGameLists->rankedByCategory($categoryId);
        $bindings['UnrankedGameList'] = $this->repoGameLists->unrankedByCategory($categoryId);
        $bindings['DelistedGameList'] = $this->repoGameLists->delistedByCategory($categoryId);

        // Snapshot
        //$bindings['SnapshotTopRated'] = $this->repoGameLists->rankedByCategory($categoryId, 10);
        //$bindings['SnapshotNewReleases'] = $this->repoGameLists->recentlyReleasedByCategory($categoryId, 10);
        //$bindings['SnapshotUnranked'] = $this->repoGameLists->unrankedByCategory($categoryId, 10);

        // Tables
        $bindings['RankedListSort'] = "[4, 'desc']";
        $bindings['UnrankedListSort'] = "[3, 'desc'], [1, 'asc']";

        $pageTitle = 'Nintendo Switch '.$categoryName;
        if (substr($categoryName, -4) == 'game') {
            $pageTitle .= 's';
        } else {
            $pageTitle .= ' games';
        }

        $bindings['PageTitle'] = $pageTitle;
        $bindings['TopTitle'] = $pageTitle;

        if ($category->parent_id) {
            $categoryParent = $this->getServiceCategory()->find($category->parent_id);
            if (!$categoryParent) abort(500);
            $bindings['crumbNav'] = $this->viewBreadcrumbs->gamesBySubcategorySubpage($categoryParent, $categoryName);
        } else {
            $bindings['crumbNav'] = $this->viewBreadcrumbs->gamesByCategorySubpage($categoryName);
        }

        return view('games.browse.category.page-landing', $bindings);
    }
}
