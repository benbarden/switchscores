<?php

namespace App\Http\Controllers\PublicSite\Console;

use App\Domain\Category\Repository as CategoryRepository;

use App\Models\Console;
use App\Models\Category;

use Illuminate\Routing\Controller as Controller;

class BrowseByCategoryController extends Controller
{
    public function __construct(
        private CategoryRepository $repoCategory
    )
    {
    }

    public function landing(Console $console)
    {
        $pageTitle = 'Nintendo '.$console->name.' games list - By category';
        $breadcrumbs = resolve('View/Breadcrumbs/MainSite')->consoleSubpage('By category', $console);
        $bindings = resolve('View/Bindings/MainSite')->setBreadcrumbs($breadcrumbs)->generateMain($pageTitle);

        $bindings['CategoryList'] = $this->repoCategory->topLevelCategories();

        $bindings['Console'] = $console;

        return view('public.console.by-category.landing', $bindings);
    }

    public function page(Console $console, $category)
    {
        $category = $this->repoCategory->getByLinkTitle($category);
        if (!$category) abort(404);

        $consoleId = $console->id;
        $categoryId = $category->id;
        $categoryName = $category->name;

        $pageTitle = 'Nintendo '.$console->name.' '.$categoryName;
        if (str_ends_with($categoryName, 'game')) {
            $pageTitle .= 's';
        } else {
            $pageTitle .= ' games';
        }

        if ($category->parent_id) {
            $categoryParent = $this->repoCategory->find($category->parent_id);
            if (!$categoryParent) abort(500);
            $breadcrumbs = resolve('View/Breadcrumbs/MainSite')->consoleSubcategorySubpage($categoryParent, $categoryName, $console);
        } else {
            $breadcrumbs = resolve('View/Breadcrumbs/MainSite')->consoleCategorySubpage($categoryName, $console);
        }

        $bindings = resolve('View/Bindings/MainSite')->setBreadcrumbs($breadcrumbs)->generateMain($pageTitle);

        $bindings['Console'] = $console;
        $bindings['Category'] = $category;

        // Lists
        $bindings['RankedGameList'] = $this->repoCategory->rankedByCategory($consoleId, $categoryId);
        $bindings['UnrankedGameList'] = $this->repoCategory->unrankedByCategory($consoleId, $categoryId);
        $bindings['DelistedGameList'] = $this->repoCategory->delistedByCategory($consoleId, $categoryId);
        $bindings['LowQualityGameList'] = $this->repoCategory->lowQualityByCategory($consoleId, $categoryId);

        // Tables
        $bindings['RankedListSort'] = "[4, 'desc']";
        $bindings['UnrankedListSort'] = "[3, 'desc'], [1, 'asc']";

        return view('public.console.by-category.page', $bindings);
    }
}
