<?php

namespace App\Http\Controllers\PublicSite\Browse;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Http\Request;

use App\Domain\View\Breadcrumbs\PublicBreadcrumbs;
use App\Domain\View\PageBuilders\PublicPageBuilder;
use App\Domain\GameCollection\Repository as GameCollectionRepository;
use App\Models\Console;

class BrowseByCollectionController extends Controller
{
    public function __construct(
        private PublicPageBuilder $pageBuilder,
        private GameCollectionRepository $repoGameCollection,
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
        $pageTitle = 'Browse games by collection';
        $bindings = $this->pageBuilder->build($pageTitle, PublicBreadcrumbs::browseCollectionLanding())->bindings;

        $bindings['CollectionList'] = $this->repoGameCollection->getAll();

        return view('public.browse.by-collection.landing', $bindings);
    }

    public function page(Request $request, $collection)
    {
        $gameCollection = $this->repoGameCollection->getByLinkTitle($collection);
        if (!$gameCollection) abort(404);

        $collectionId   = $gameCollection->id;
        $collectionName = $gameCollection->name;
        $consoleId      = $this->resolveConsoleId($request);
        $consoleSlug    = $request->get('console');

        $pageTitle = $collectionName.' games';
        $bindings = $this->pageBuilder->build($pageTitle, PublicBreadcrumbs::browseCollectionPage($collectionName))->bindings;

        $bindings['GameCollection'] = $gameCollection;
        $bindings['ConsoleSlug']    = $consoleSlug;

        $bindings['Stats']      = $this->repoGameCollection->getSnapshotStatsByCollectionMerged($collectionId, $consoleId);
        $bindings['TopRated']   = $this->repoGameCollection->rankedByCollectionMerged($collectionId, $consoleId, 12);
        $bindings['HiddenGems'] = $this->repoGameCollection->hiddenGemsByCollectionMerged($collectionId, $consoleId, 12);

        if ($gameCollection->meta_description) {
            $bindings['MetaDescription'] = $gameCollection->meta_description;
        }

        return view('public.browse.by-collection.page', $bindings);
    }

    public function list(Request $request, $collection)
    {
        $gameCollection = $this->repoGameCollection->getByLinkTitle($collection);
        if (!$gameCollection) abort(404);

        $collectionId   = $gameCollection->id;
        $collectionName = $gameCollection->name;
        $consoleId      = $this->resolveConsoleId($request);
        $consoleSlug    = $request->get('console');

        $pageTitle = 'List of '.$collectionName.' games';
        $bindings = $this->pageBuilder->build($pageTitle, PublicBreadcrumbs::browseCollectionList($collectionName, $gameCollection->link_title))->bindings;

        $bindings['GameCollection'] = $gameCollection;
        $bindings['ConsoleSlug']    = $consoleSlug;

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

        $bindings['Games']        = $this->repoGameCollection->listByCollectionMerged($collectionId, $page, $perPage, $filter, $sort, $consoleId);
        $bindings['sort']         = $sort;
        $bindings['filter']       = $filter;
        $bindings['CanonicalUrl'] = route('browse.byCollection.list', ['collection' => $gameCollection->link_title]);

        return view('public.browse.by-collection.list', $bindings);
    }
}
