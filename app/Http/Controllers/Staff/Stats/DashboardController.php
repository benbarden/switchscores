<?php

namespace App\Http\Controllers\Staff\Stats;

use Illuminate\Routing\Controller as Controller;

use App\Domain\View\Breadcrumbs\StaffBreadcrumbs;
use App\Domain\View\PageBuilders\StaffPageBuilder;

use App\Domain\GameStats\Repository as GameStatsRepository;

class DashboardController extends Controller
{
    public function __construct(
        private StaffPageBuilder $pageBuilder,
        private GameStatsRepository $repoGameStats
    )
    {
    }

    public function show()
    {
        $pageTitle = 'Stats dashboard';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::statsDashboard())->bindings;

        $bindings['TotalGameCount'] = $this->repoGameStats->grandTotal();
        $bindings['ReleasedGameCount'] = $this->repoGameStats->totalReleased();
        $bindings['UpcomingGameCount'] = $this->repoGameStats->totalUpcoming();

        return view('staff.stats.dashboard', $bindings);
    }
}
