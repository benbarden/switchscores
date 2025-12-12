<?php

namespace App\Http\Controllers\PublicSite\Console;

use App\Domain\GameSeries\Repository as GameSeriesRepository;
use App\Domain\ViewBreadcrumbs\MainSite as Breadcrumbs;

use App\Models\Console;

use Illuminate\Routing\Controller as Controller;

class BrowseBySeriesController extends Controller
{
    public function __construct(
        private GameSeriesRepository $repoGameSeries,
        private Breadcrumbs $viewBreadcrumbs
    )
    {
    }

    public function landing(Console $console)
    {
        $bindings = [];

        $bindings['SeriesList'] = $this->repoGameSeries->getAllWithGames($console);

        $bindings['Console'] = $console;

        $bindings['PageTitle'] = 'Nintendo '.$console->name.' games list - By series';
        $bindings['TopTitle'] = 'Nintendo '.$console->name.' games list - By series';
        $bindings['crumbNav'] = $this->viewBreadcrumbs->consoleSubpage('By series', $console);

        return view('public.console.by-series.landing', $bindings);
    }

    public function page(Console $console, $series)
    {
        $bindings = [];

        $gameSeries = $this->repoGameSeries->getByLinkTitle($series);
        if (!$gameSeries) abort(404);

        $consoleId = $console->id;
        $seriesId = $gameSeries->id;
        $seriesName = $gameSeries->series;

        $gameList = $this->repoGameSeries->gamesBySeries($consoleId, $seriesId);

        // Lists
        $bindings['RankedGameList'] = $this->repoGameSeries->rankedBySeries($consoleId, $seriesId);
        $bindings['UnrankedGameList'] = $this->repoGameSeries->unrankedBySeries($consoleId, $seriesId);
        $bindings['DelistedGameList'] = $this->repoGameSeries->delistedBySeries($consoleId, $seriesId);
        $bindings['LowQualityGameList'] = $this->repoGameSeries->lowQualityBySeries($consoleId, $seriesId);

        $bindings['GameList'] = $gameList;
        $bindings['Console'] = $console;

        $bindings['PageTitle'] = 'Nintendo '.$console->name.' games list - By series: '.$seriesName;
        $bindings['TopTitle'] = 'Nintendo '.$console->name.' games list - By series: '.$seriesName;
        $bindings['crumbNav'] = $this->viewBreadcrumbs->consoleSeriesSubpage($seriesName, $console);

        return view('public.console.by-series.page', $bindings);
    }
}
