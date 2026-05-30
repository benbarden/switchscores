<?php

namespace App\Http\Controllers\PublicSite\Browse;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Http\Request;

use App\Domain\View\Breadcrumbs\PublicBreadcrumbs;
use App\Domain\View\PageBuilders\PublicPageBuilder;
use App\Domain\GameSeries\Repository as GameSeriesRepository;
use App\Models\Console;

class BrowseBySeriesController extends Controller
{
    public function __construct(
        private PublicPageBuilder $pageBuilder,
        private GameSeriesRepository $repoGameSeries,
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
        $pageTitle = 'Browse games by series';
        $bindings = $this->pageBuilder->build($pageTitle, PublicBreadcrumbs::browseSeriesLanding())->bindings;

        $bindings['SeriesList'] = $this->repoGameSeries->getAll();

        return view('public.browse.by-series.landing', $bindings);
    }

    public function page(Request $request, $series)
    {
        $gameSeries = $this->repoGameSeries->getByLinkTitle($series);
        if (!$gameSeries) abort(404);

        $seriesId   = $gameSeries->id;
        $seriesName = $gameSeries->series;
        $consoleId  = $this->resolveConsoleId($request);
        $consoleSlug = $request->get('console');

        $pageTitle = $seriesName.' games';
        $bindings = $this->pageBuilder->build($pageTitle, PublicBreadcrumbs::browseSeriesPage($seriesName))->bindings;

        $bindings['GameSeries']  = $gameSeries;
        $bindings['ConsoleSlug'] = $consoleSlug;

        $bindings['Stats']      = $this->repoGameSeries->getSnapshotStatsBySeriesMerged($seriesId, $consoleId);
        $bindings['TopRated']   = $this->repoGameSeries->rankedBySeriesMerged($seriesId, $consoleId, 12);
        $bindings['HiddenGems'] = $this->repoGameSeries->hiddenGemsBySeriesMerged($seriesId, $consoleId, 12);

        if ($gameSeries->meta_description) {
            $bindings['MetaDescription'] = $gameSeries->meta_description;
        }

        return view('public.browse.by-series.page', $bindings);
    }

    public function list(Request $request, $series)
    {
        $gameSeries = $this->repoGameSeries->getByLinkTitle($series);
        if (!$gameSeries) abort(404);

        $seriesId   = $gameSeries->id;
        $seriesName = $gameSeries->series;
        $consoleId  = $this->resolveConsoleId($request);
        $consoleSlug = $request->get('console');

        $pageTitle = 'List of '.$seriesName.' games';
        $bindings = $this->pageBuilder->build($pageTitle, PublicBreadcrumbs::browseSeriesList($seriesName, $gameSeries->link_title))->bindings;

        $bindings['GameSeries']  = $gameSeries;
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

        $bindings['Games']        = $this->repoGameSeries->listBySeriesMerged($seriesId, $page, $perPage, $filter, $sort, $consoleId);
        $bindings['sort']         = $sort;
        $bindings['filter']       = $filter;
        $bindings['CanonicalUrl'] = route('browse.bySeries.list', ['series' => $gameSeries->link_title]);

        return view('public.browse.by-series.list', $bindings);
    }
}
