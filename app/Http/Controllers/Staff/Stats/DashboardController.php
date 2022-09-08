<?php

namespace App\Http\Controllers\Staff\Stats;

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
        $pageTitle = 'Stats dashboard';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->topLevelPage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['TotalGameCount'] = $this->repoGameStats->grandTotal();
        $bindings['ReleasedGameCount'] = $this->repoGameStats->totalReleased();
        $bindings['UpcomingGameCount'] = $this->repoGameStats->totalUpcoming();

        return view('staff.stats.dashboard', $bindings);
    }
}
