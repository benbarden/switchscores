<?php

namespace App\Http\Controllers\PublicSite\Browse;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Http\Request;

use App\Domain\View\Breadcrumbs\PublicBreadcrumbs;
use App\Domain\View\PageBuilders\PublicPageBuilder;
use App\Domain\Category\Repository as CategoryRepository;
use App\Models\Console;

class BrowseByCategoryController extends Controller
{
    public function __construct(
        private PublicPageBuilder $pageBuilder,
        private CategoryRepository $repoCategory
    ) {
    }

    private function resolveConsoleId(Request $request): ?int
    {
        return match($request->get('console')) {
            'switch-1' => Console::ID_SWITCH_1,
            'switch-2' => Console::ID_SWITCH_2,
            default    => null,
        };
    }

    public function landing()
    {
        $pageTitle = 'Browse games by category';
        $bindings = $this->pageBuilder->build($pageTitle, PublicBreadcrumbs::browseCategoryLanding())->bindings;

        $bindings['CategoryList'] = $this->repoCategory->topLevelCategories();

        return view('public.browse.by-category.landing', $bindings);
    }

    public function page(Request $request, $category)
    {
        $category = $this->repoCategory->getByLinkTitle($category);
        if (!$category) abort(404);

        $categoryId   = $category->id;
        $categoryName = $category->name;
        $consoleId    = $this->resolveConsoleId($request);
        $consoleSlug  = $request->get('console');

        $pageTitle = $categoryName.' games';

        if ($category->parent_id) {
            $categoryParent = $this->repoCategory->find($category->parent_id);
            $bindings = $this->pageBuilder->build($pageTitle, PublicBreadcrumbs::browseCategoryPage($categoryName, $categoryParent))->bindings;
        } else {
            $bindings = $this->pageBuilder->build($pageTitle, PublicBreadcrumbs::browseCategoryPage($categoryName))->bindings;
        }

        $bindings['Category']    = $category;
        $bindings['ConsoleSlug'] = $consoleSlug;

        $bindings['Stats']      = $this->repoCategory->getSnapshotStatsMerged($category, $consoleId);
        $bindings['TopRated']   = $this->repoCategory->rankedByCategoryMerged($categoryId, $consoleId, 12);
        $bindings['HiddenGems'] = $this->repoCategory->hiddenGemsByCategoryMerged($categoryId, $consoleId, 12);

        if ($category->meta_description) {
            $bindings['MetaDescription'] = $category->meta_description;
        }

        return view('public.browse.by-category.page', $bindings);
    }

    public function list(Request $request, $category)
    {
        $category = $this->repoCategory->getByLinkTitle($category);
        if (!$category) abort(404);

        $categoryId   = $category->id;
        $categoryName = $category->name;
        $consoleId    = $this->resolveConsoleId($request);
        $consoleSlug  = $request->get('console');

        $pageTitle = 'List of '.$categoryName.' games';

        if ($category->parent_id) {
            $categoryParent = $this->repoCategory->find($category->parent_id);
            $bindings = $this->pageBuilder->build($pageTitle, PublicBreadcrumbs::browseCategoryList('List', $category, $categoryParent))->bindings;
        } else {
            $bindings = $this->pageBuilder->build($pageTitle, PublicBreadcrumbs::browseCategoryList('List', $category))->bindings;
        }

        $bindings['Category']    = $category;
        $bindings['ConsoleSlug'] = $consoleSlug;

        // Filters
        $allowedFilters = ['ranked', 'hidden', 'noreviews'];
        $filter = $request->get('filter', 'ranked');
        if (!in_array($filter, $allowedFilters)) {
            $filter = 'ranked';
        }
        $defaultSort = $filter == 'noreviews' ? 'release_desc' : 'rating_desc';

        // Sorting
        $allowedSorts = ['title_asc', 'title_desc', 'rating_desc', 'rating_asc', 'release_desc', 'release_asc'];
        $sort = $request->get('sort', $defaultSort);
        if (!in_array($sort, $allowedSorts)) {
            $sort = $defaultSort;
        }

        // Pagination
        $page    = max((int) $request->get('page', 1), 1);
        $perPage = 36;

        $bindings['Games']        = $this->repoCategory->listByCategoryMerged($categoryId, $page, $perPage, $filter, $sort, $consoleId);
        $bindings['sort']         = $sort;
        $bindings['filter']       = $filter;
        $bindings['CanonicalUrl'] = route('browse.byCategory.list', ['category' => $category->link_title]);

        return view('public.browse.by-category.list', $bindings);
    }
}
