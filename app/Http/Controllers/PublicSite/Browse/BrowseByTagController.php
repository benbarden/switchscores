<?php

namespace App\Http\Controllers\PublicSite\Browse;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Http\Request;

use App\Domain\View\Breadcrumbs\PublicBreadcrumbs;
use App\Domain\View\PageBuilders\PublicPageBuilder;
use App\Domain\Tag\Repository as TagRepository;
use App\Domain\TagCategory\Repository as TagCategoryRepository;
use App\Models\Console;

class BrowseByTagController extends Controller
{
    public function __construct(
        private PublicPageBuilder $pageBuilder,
        private TagRepository $repoTag,
        private TagCategoryRepository $repoTagCategory,
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
        $pageTitle = 'Browse games by tag';
        $bindings = $this->pageBuilder->build($pageTitle, PublicBreadcrumbs::browseTagLanding())->bindings;

        $bindings['TagCategoryList'] = $this->repoTagCategory->getAll();

        return view('public.browse.by-tag.landing', $bindings);
    }

    public function page(Request $request, $tag)
    {
        $tag = $this->repoTag->getByLinkTitle($tag);
        if (!$tag) abort(404);

        $tagId       = $tag->id;
        $tagName     = $tag->tag_name;
        $consoleId   = $this->resolveConsoleId($request);
        $consoleSlug = $request->get('console');

        $pageTitle = $tagName.' games';
        $bindings = $this->pageBuilder->build($pageTitle, PublicBreadcrumbs::browseTagPage($tagName))->bindings;

        $bindings['Tag']         = $tag;
        $bindings['ConsoleSlug'] = $consoleSlug;

        $bindings['Stats']      = $this->repoTag->getSnapshotStatsByTagMerged($tagId, $consoleId);
        $bindings['TopRated']   = $this->repoTag->rankedByTagMerged($tagId, $consoleId, 12);
        $bindings['HiddenGems'] = $this->repoTag->hiddenGemsByTagMerged($tagId, $consoleId, 12);

        if ($tag->meta_description) {
            $bindings['MetaDescription'] = $tag->meta_description;
        }

        return view('public.browse.by-tag.page', $bindings);
    }

    public function list(Request $request, $tag)
    {
        $tag = $this->repoTag->getByLinkTitle($tag);
        if (!$tag) abort(404);

        $tagId       = $tag->id;
        $tagName     = $tag->tag_name;
        $consoleId   = $this->resolveConsoleId($request);
        $consoleSlug = $request->get('console');

        $pageTitle = 'List of '.$tagName.' games';
        $bindings = $this->pageBuilder->build($pageTitle, PublicBreadcrumbs::browseTagList($tagName, $tag->link_title))->bindings;

        $bindings['Tag']         = $tag;
        $bindings['ConsoleSlug'] = $consoleSlug;

        $allowedFilters = ['ranked', 'hidden', 'noreviews'];
        $filter = $request->get('filter', 'ranked');
        if (!in_array($filter, $allowedFilters)) {
            $filter = 'ranked';
        }
        $defaultSort = $filter == 'noreviews' ? 'release_desc' : 'rating_desc';

        $allowedSorts = ['title_asc', 'title_desc', 'rating_desc', 'rating_asc', 'release_desc', 'release_asc'];
        $sort = $request->get('sort', $defaultSort);
        if (!in_array($sort, $allowedSorts)) {
            $sort = $defaultSort;
        }

        $page    = max((int) $request->get('page', 1), 1);
        $perPage = 36;

        $bindings['Games']        = $this->repoTag->listByTagMerged($tagId, $page, $perPage, $filter, $sort, $consoleId);
        $bindings['sort']         = $sort;
        $bindings['filter']       = $filter;
        $bindings['CanonicalUrl'] = route('browse.byTag.list', ['tag' => $tag->link_title]);

        return view('public.browse.by-tag.list', $bindings);
    }
}
