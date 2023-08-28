<?php

namespace App\Http\Controllers\Staff\Games;

use Illuminate\Routing\Controller as Controller;

use App\Domain\FeaturedGame\Repository as FeaturedGameRepository;
use App\Domain\GameStats\Repository as GameStatsRepository;

use App\Traits\SwitchServices;

class DashboardController extends Controller
{
    use SwitchServices;

    protected $repoFeaturedGames;
    protected $repoGameStats;

    public function __construct(
        FeaturedGameRepository $featuredGames,
        GameStatsRepository $repoGameStats
    )
    {
        $this->repoFeaturedGames = $featuredGames;
        $this->repoGameStats = $repoGameStats;
    }

    public function show()
    {
        $pageTitle = 'Games dashboard';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->gamesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        // Games to release
        $bindings['GamesForReleaseCount'] = $this->repoGameStats->totalToBeReleased();

        // Missing data
        $bindings['NoNintendoCoUkLinkCount'] = $this->getServiceGame()->getWithNoNintendoCoUkLink()->count();
        $bindings['BrokenNintendoCoUkLinkCount'] = $this->getServiceGame()->getWithBrokenNintendoCoUkLink()->count();
        $bindings['NoPriceCount'] = $this->getServiceGame()->countWithoutPrices();
        $bindings['MissingVideoTypeCount'] = $this->repoGameStats->totalNoVideoType();
        $bindings['MissingAmazonUkLink'] = $this->getServiceGame()->countWithNoAmazonUkLink();

        // Release date stats
        $bindings['TotalGameCount'] = $this->repoGameStats->grandTotal();
        $bindings['ReleasedGameCount'] = $this->repoGameStats->totalReleased();
        $bindings['UpcomingGameCount'] = $this->repoGameStats->totalUpcoming();

        // Format stats
        $bindings['FormatDigital'] = $this->repoGameStats->getFormatDigital();
        $bindings['FormatPhysical'] = $this->repoGameStats->getFormatPhysical();
        $bindings['FormatDLC'] = $this->repoGameStats->getFormatDLC();
        $bindings['FormatDemo'] = $this->repoGameStats->getFormatDemo();

        return view('staff.games.dashboard', $bindings);
    }
}
