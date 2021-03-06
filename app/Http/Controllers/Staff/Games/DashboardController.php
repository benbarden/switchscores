<?php

namespace App\Http\Controllers\Staff\Games;

use Illuminate\Routing\Controller as Controller;

use App\Domain\ViewBreadcrumbs\Staff as Breadcrumbs;

use App\Domain\FeaturedGame\Repository as FeaturedGameRepository;
use App\Domain\GameStats\Repository as GameStatsRepository;

use App\Traits\SwitchServices;
use App\Traits\StaffView;

class DashboardController extends Controller
{
    use SwitchServices;
    use StaffView;

    protected $viewBreadcrumbs;
    protected $repoFeaturedGames;
    protected $repoGameStats;

    public function __construct(
        Breadcrumbs $viewBreadcrumbs,
        FeaturedGameRepository $featuredGames,
        GameStatsRepository $repoGameStats
    )
    {
        $this->viewBreadcrumbs = $viewBreadcrumbs;
        $this->repoFeaturedGames = $featuredGames;
        $this->repoGameStats = $repoGameStats;
    }

    public function show()
    {
        $bindings = $this->getBindings('Games dashboard');

        $bindings['crumbNav'] = $this->viewBreadcrumbs->topLevelPage('Games dashboard');

        $serviceGameReleaseDate = $this->getServiceGameReleaseDate();

        // Games to release
        $bindings['GamesForReleaseCount'] = $this->repoGameStats->totalToBeReleased();

        // Missing data
        $bindings['NoNintendoCoUkLinkCount'] = $this->getServiceGame()->getWithNoNintendoCoUkLink()->count();
        $bindings['BrokenNintendoCoUkLinkCount'] = $this->getServiceGame()->getWithBrokenNintendoCoUkLink()->count();
        $bindings['NoPriceCount'] = $this->getServiceGame()->countWithoutPrices();
        $bindings['MissingVideoTypeCount'] = $this->repoGameStats->totalNoVideoType();
        $bindings['MissingAmazonUkLink'] = $this->getServiceGame()->countWithNoAmazonUkLink();

        // Release date stats
        $bindings['TotalGameCount'] = $this->getServiceGame()->getCount();
        $bindings['ReleasedGameCount'] = $this->repoGameStats->totalReleased();
        $bindings['UpcomingGameCount'] = $serviceGameReleaseDate->countUpcoming();

        // Format stats
        $bindings['FormatOptionTable'] = $this->repoGameStats->tableFormatOptions();

        return view('staff.games.dashboard', $bindings);
    }
}
