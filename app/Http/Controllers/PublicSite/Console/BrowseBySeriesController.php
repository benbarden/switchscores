<?php

namespace App\Http\Controllers\PublicSite\Console;

use Illuminate\Routing\Controller as Controller;

use App\Domain\View\Breadcrumbs\PublicBreadcrumbs;
use App\Domain\View\PageBuilders\PublicPageBuilder;

use App\Domain\GameSeries\Repository as GameSeriesRepository;

use App\Models\Console;

class BrowseBySeriesController extends Controller
{
    public function __construct(
        private PublicPageBuilder $pageBuilder,
        private GameSeriesRepository $repoGameSeries,
    )
    {
    }

    public function landing(Console $console)
    {
        $pageTitle = 'Nintendo '.$console->name.' games list - By series';
        $bindings = $this->pageBuilder->build($pageTitle, PublicBreadcrumbs::consoleSubpage('By series', $console))->bindings;

        $bindings['SeriesList'] = $this->repoGameSeries->getAllWithGames($console);

        $bindings['Console'] = $console;

        return view('public.console.by-series.landing', $bindings);
    }

    public function page(Console $console, $series)
    {
        $gameSeries = $this->repoGameSeries->getByLinkTitle($series);
        if (!$gameSeries) abort(404);

        $consoleId = $console->id;
        $seriesId = $gameSeries->id;
        $seriesName = $gameSeries->series;

        $gameList = $this->repoGameSeries->gamesBySeries($consoleId, $seriesId);

        $pageTitle = 'Nintendo '.$console->name.' games list - By series: '.$seriesName;
        $bindings = $this->pageBuilder->build($pageTitle, PublicBreadcrumbs::consoleSeriesSubpage($seriesName, $console))->bindings;

        // Lists
        $bindings['RankedGameList'] = $this->repoGameSeries->rankedBySeries($consoleId, $seriesId);
        $bindings['UnrankedGameList'] = $this->repoGameSeries->unrankedBySeries($consoleId, $seriesId);
        $bindings['DelistedGameList'] = $this->repoGameSeries->delistedBySeries($consoleId, $seriesId);
        $bindings['LowQualityGameList'] = $this->repoGameSeries->lowQualityBySeries($consoleId, $seriesId);

        $bindings['GameList'] = $gameList;
        $bindings['Console'] = $console;

        return view('public.console.by-series.page', $bindings);
    }
}
