<?php

namespace App\Http\Controllers\PublicSite\Games;

use App\Domain\GameSeries\Repository as GameSeriesRepository;
use App\Domain\ViewBreadcrumbs\MainSite as Breadcrumbs;

use Illuminate\Routing\Controller as Controller;

class BrowseBySeriesController extends Controller
{
    public function __construct(
        private GameSeriesRepository $repoGameSeries,
        private Breadcrumbs $viewBreadcrumbs
    )
    {
    }

    public function landing()
    {
        $bindings = [];

        $bindings['SeriesList'] = $this->repoGameSeries->getAll();

        $bindings['PageTitle'] = 'Nintendo Switch games list - By series';
        $bindings['TopTitle'] = 'Nintendo Switch games list - By series';
        $bindings['crumbNav'] = $this->viewBreadcrumbs->gamesSubpage('By series');

        return view('public.games.browse.series.landing', $bindings);
    }

    public function page($series)
    {
        $bindings = [];

        $gameSeries = $this->repoGameSeries->getByLinkTitle($series);
        if (!$gameSeries) abort(404);

        $seriesId = $gameSeries->id;
        $seriesName = $gameSeries->series;

        $gameList = $this->repoGameSeries->gamesBySeries($seriesId);

        $bindings['GameList'] = $gameList;

        $bindings['PageTitle'] = 'Nintendo Switch games list - By series: '.$seriesName;
        $bindings['TopTitle'] = 'Nintendo Switch games list - By series: '.$seriesName;
        $bindings['crumbNav'] = $this->viewBreadcrumbs->gamesBySeriesSubpage($seriesName);

        return view('public.games.browse.series.page', $bindings);
    }
}
