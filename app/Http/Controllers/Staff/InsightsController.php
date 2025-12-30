<?php

namespace App\Http\Controllers\Staff;

use Illuminate\Routing\Controller as Controller;

use App\Domain\View\Breadcrumbs\StaffBreadcrumbs;
use App\Domain\View\PageBuilders\StaffPageBuilder;

use App\Domain\Gsc\Snapshot\Repository\GscPageSnapshotRepository;

class InsightsController extends Controller
{
    public function __construct(
        private StaffPageBuilder $pageBuilder,
        private GscPageSnapshotRepository $repoGscPageSnapshot,
    )
    {
    }

    public function index()
    {
        $pageTitle = 'Insights';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::insightsDashboard())->bindings;

        $categories = $this->repoGscPageSnapshot->latestSnapshotForPageType('category');
        $topRated   = $this->repoGscPageSnapshot->latestSnapshotForPageType('top_rated');
        $games      = $this->repoGscPageSnapshot->latestGamesSnapshot();

        $bindings['CategoryList'] = $categories['rows'];
        $bindings['TopRatedList'] = $topRated['rows'];
        $bindings['GamesList'] = $games['rows'];

        $bindings['SnapshotDate'] = $categories['snapshot_date'];
        $bindings['WindowDays']   = $categories['window_days'];

        return view('staff.insights.index', $bindings);
    }
}
