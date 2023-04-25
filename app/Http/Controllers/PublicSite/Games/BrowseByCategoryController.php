<?php

namespace App\Http\Controllers\PublicSite\Games;

use App\Domain\Category\Repository as CategoryRepository;
use App\Domain\GameLists\Repository as GameListsRepository;
use App\Domain\ViewBreadcrumbs\MainSite as Breadcrumbs;
use Illuminate\Routing\Controller as Controller;

class BrowseByCategoryController extends Controller
{
    private $repoGameLists;
    private $repoCategory;
    private $viewBreadcrumbs;

    public function __construct(
        GameListsRepository $repoGameLists,
        CategoryRepository $repoCategory,
        Breadcrumbs $viewBreadcrumbs
    )
    {
        $this->repoGameLists = $repoGameLists;
        $this->repoCategory = $repoCategory;
        $this->viewBreadcrumbs = $viewBreadcrumbs;
    }

    public function landing()
    {
        $bindings = [];

        $bindings['CategoryList'] = $this->repoCategory->topLevelCategories();

        $bindings['PageTitle'] = 'Browse Nintendo Switch games by category';
        $bindings['TopTitle'] = 'Browse Nintendo Switch games by category';
        $bindings['crumbNav'] = $this->viewBreadcrumbs->gamesSubpage('By category');

        return view('public.games.browse.byCategoryLanding', $bindings);
    }

    public function page($category)
    {
        $bindings = [];

        $category = $this->repoCategory->getByLinkTitle($category);
        if (!$category) abort(404);

        $categoryId = $category->id;
        $categoryName = $category->name;

        $bindings['Category'] = $category;

        // Lists
        $bindings['RankedGameList'] = $this->repoCategory->rankedByCategory($categoryId);
        $bindings['UnrankedGameList'] = $this->repoCategory->unrankedByCategory($categoryId);
        $bindings['DelistedGameList'] = $this->repoCategory->delistedByCategory($categoryId);

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
            $categoryParent = $this->repoCategory->find($category->parent_id);
            if (!$categoryParent) abort(500);
            $bindings['crumbNav'] = $this->viewBreadcrumbs->gamesBySubcategorySubpage($categoryParent, $categoryName);
        } else {
            $bindings['crumbNav'] = $this->viewBreadcrumbs->gamesByCategorySubpage($categoryName);
        }

        return view('public.games.browse.category.page-landing', $bindings);
    }
}
