<?php

namespace App\Http\Controllers\PublicSite\Games;

use App\Domain\Category\Repository as CategoryRepository;

use Illuminate\Routing\Controller as Controller;

class BrowseByCategoryController extends Controller
{
    public function __construct(
        private CategoryRepository $repoCategory
    )
    {
    }

    public function landing()
    {
        $pageTitle = 'Nintendo Switch games list - By category';
        $breadcrumbs = resolve('View/Breadcrumbs/MainSite')->gamesSubpage('By category');
        $bindings = resolve('View/Bindings/MainSite')->setBreadcrumbs($breadcrumbs)->generateMain($pageTitle);

        $bindings['CategoryList'] = $this->repoCategory->topLevelCategories();

        return view('public.games.browse.category.landing', $bindings);
    }

    public function page($category)
    {
        $category = $this->repoCategory->getByLinkTitle($category);
        if (!$category) abort(404);

        $categoryId = $category->id;
        $categoryName = $category->name;

        $pageTitle = 'Nintendo Switch '.$categoryName;
        if (str_ends_with($categoryName, 'game')) {
            $pageTitle .= 's';
        } else {
            $pageTitle .= ' games';
        }

        if ($category->parent_id) {
            $categoryParent = $this->repoCategory->find($category->parent_id);
            if (!$categoryParent) abort(500);
            $breadcrumbs = resolve('View/Breadcrumbs/MainSite')->gamesBySubcategorySubpage($categoryParent, $categoryName);
        } else {
            $breadcrumbs = resolve('View/Breadcrumbs/MainSite')->gamesByCategorySubpage($categoryName);
        }

        $bindings = resolve('View/Bindings/MainSite')->setBreadcrumbs($breadcrumbs)->generateMain($pageTitle);

        $bindings['Category'] = $category;

        // Lists
        $bindings['RankedGameList'] = $this->repoCategory->rankedByCategory($categoryId);
        $bindings['UnrankedGameList'] = $this->repoCategory->unrankedByCategory($categoryId);
        $bindings['DelistedGameList'] = $this->repoCategory->delistedByCategory($categoryId);

        // Tables
        $bindings['RankedListSort'] = "[4, 'desc']";
        $bindings['UnrankedListSort'] = "[3, 'desc'], [1, 'asc']";

        return view('public.games.browse.category.page', $bindings);
    }
}
