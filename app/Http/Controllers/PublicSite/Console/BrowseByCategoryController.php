<?php

namespace App\Http\Controllers\PublicSite\Console;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Http\Request;

use App\Domain\View\Breadcrumbs\PublicBreadcrumbs;
use App\Domain\View\PageBuilders\PublicPageBuilder;

use App\Domain\Category\Repository as CategoryRepository;

use App\Enums\LayoutVersion;
use App\Models\Console;

class BrowseByCategoryController extends Controller
{
    public function __construct(
        private PublicPageBuilder $pageBuilder,
        private CategoryRepository $repoCategory
    )
    {
    }

    public function landing(Console $console)
    {
        $pageTitle = 'Nintendo '.$console->name.' games list - By category';
        $bindings = $this->pageBuilder->build($pageTitle, PublicBreadcrumbs::consoleSubpage('By category', $console))->bindings;

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
            $bindings = $this->pageBuilder->build($pageTitle, PublicBreadcrumbs::consoleCategoryPage($categoryName, $console, $categoryParent))->bindings;
        } else {
            $bindings = $this->pageBuilder->build($pageTitle, PublicBreadcrumbs::consoleCategoryPage($categoryName, $console))->bindings;
        }

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

        // Canonical URL
        $bindings['CanonicalUrl'] = route('console.byCategory.page',
            ['console' => $console, 'category' => $category->link_title]);

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
            $bindings = $this->pageBuilder->build($pageTitle, PublicBreadcrumbs::consoleCategorySubpage('List', $console, $category, $categoryParent))->bindings;
        } else {
            $bindings = $this->pageBuilder->build($pageTitle, PublicBreadcrumbs::consoleCategorySubpage('List', $console, $category))->bindings;
        }

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

        // Canonical URL
        if ($filter) {
            $filterSuffix = '?filter='.$filter;
        } else {
            $filterSuffix = '';
        }
        $canonicalUrl = route('console.byCategory.list',
                ['console' => $console, 'category' => $category->link_title]).$filterSuffix;
        if ($page > 1) {
            $canonicalUrl .= '&page='.$page;
        }
        $bindings['CanonicalUrl'] = $canonicalUrl;

        return view('public.console.by-category.page-list-v2', $bindings);
    }
}
