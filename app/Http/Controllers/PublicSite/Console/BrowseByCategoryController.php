<?php

namespace App\Http\Controllers\PublicSite\Console;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Http\Request;

use App\Domain\Category\Repository as CategoryRepository;

use App\Enums\LayoutVersion;
use App\Models\Console;
use App\Models\Category;
use App\Models\Game;

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

        // Meta
        if ($category->meta_description) {
            $bindings['MetaDescription'] = $category['meta_description'];
        }

        // V2: Snapshot
        $stats = $this->repoCategory->getSnapshotStats($category, $consoleId);
        $bindings['Stats'] = $stats;

        // V2: Top Rated and Hidden Gems
        $bindings['TopRated'] = $this->repoCategory->rankedByCategory($consoleId, $categoryId, 12);
        $bindings['HiddenGems'] = $this->repoCategory->hiddenGemsByCategory($consoleId, $categoryId, 12);

        if ($category->layout_version == LayoutVersion::LAYOUT_V2->value) {
            $viewFile = 'public.console.by-category.page-layout-v2';
        } else {
            $viewFile = 'public.console.by-category.page';
        }

        return view($viewFile, $bindings);
    }

    public function list(Request $request, Console $console, $category)
    {
        $category = $this->repoCategory->getByLinkTitle($category);
        if (!$category) abort(404);

        $consoleId = $console->id;
        $categoryId = $category->id;
        $categoryName = $category->name;

        $pageTitle = 'List of Nintendo '.$console->name.' '.$categoryName;
        if (str_ends_with($categoryName, 'game')) {
            $gamesSuffix = 's';
            $gamesSuffixSingular = '';
        } else {
            $gamesSuffix = ' games';
            $gamesSuffixSingular = ' game';
        }
        $pageTitle .= $gamesSuffix;

        $metaDescription = sprintf("Browse the complete list of Nintendo %s %s%s. View ratings, reviews, ".
            "release dates, and filter by ranked titles, hidden gems, or games with no reviews.",
            $console->name, $categoryName, $gamesSuffix);

        $introDescription = sprintf("Browse every Nintendo %s %s%s. ".
            "Use the filters above to view ranked titles, hidden gems, or games with no reviews.",
            $console->name, $categoryName, $gamesSuffixSingular);

        if ($category->parent_id) {
            $categoryParent = $this->repoCategory->find($category->parent_id);
            if (!$categoryParent) abort(500);
            $breadcrumbs = resolve('View/Breadcrumbs/MainSite')->consoleSubcategorySubpage($categoryParent, $categoryName, $console);
        } else {
            $breadcrumbs = resolve('View/Breadcrumbs/MainSite')->consoleCategorySubpage($categoryName, $console);
        }

        $bindings = resolve('View/Bindings/MainSite')->setBreadcrumbs($breadcrumbs)->generateMain($pageTitle);

        $bindings['MetaDescription'] = $metaDescription;
        $bindings['IntroDescription'] = $introDescription;

        // Filters
        $allowedFilters = ['ranked', 'hidden', 'noreviews'];
        $filter = $request->get('filter', 'ranked');

        if (!in_array($filter, $allowedFilters)) {
            $filter = 'ranked';
        }
        if ($filter == 'noreviews') {
            $defaultSort = 'release_desc';
        } else {
            $defaultSort = 'rating_desc';
        }

        // Sorting
        $allowedSorts = [
            'title_asc',
            'title_desc',
            'rating_desc',
            'rating_asc',
            'release_desc',
            'release_asc',
        ];

        $sort = $request->get('sort', $defaultSort);

        if (!in_array($sort, $allowedSorts)) {
            $sort = $defaultSort;
        }

        // Pagination
        $page = max((int) $request->get('page', 1), 1);
        $perPage = 36; // Good for card grids (4 columns × 9 rows)

        // Query (returns an array with keys: items, page, pages, total)
        $games = $this->repoCategory->listByCategory($consoleId, $categoryId, $page, $perPage, $filter, $sort);
        $bindings['Games'] = $games;
        $bindings['sort'] = $sort;
        $bindings['filter'] = $filter;

        $bindings['Console'] = $console;
        $bindings['Category'] = $category;

        return view('public.console.by-category.page-list-v2', $bindings);
    }
}
