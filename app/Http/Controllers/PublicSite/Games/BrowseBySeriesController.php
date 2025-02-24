<?php

namespace App\Http\Controllers\PublicSite\Games;

use App\Domain\GameLists\DbQueries as GameListsDbQueries;
use App\Domain\GameLists\Repository as GameListsRepository;
use App\Domain\GameSeries\Repository as GameSeriesRepository;
use App\Domain\ViewBreadcrumbs\MainSite as Breadcrumbs;
use App\Traits\SwitchServices;
use Illuminate\Routing\Controller as Controller;

class BrowseBySeriesController extends Controller
{
    use SwitchServices;

    public function __construct(
        private GameListsRepository $repoGameLists,
        private GameListsDbQueries $dbGameLists,
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
