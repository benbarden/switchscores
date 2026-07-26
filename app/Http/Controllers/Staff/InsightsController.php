<?php

namespace App\Http\Controllers\Staff;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
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

    public function page(Request $request)
    {
        $pageUrl = trim($request->query('url', ''));

        if (!$pageUrl) {
            return redirect(route('staff.insights.index'));
        }

        $snapshots = $this->repoGscPageSnapshot->getSnapshotsByUrl($pageUrl);

        if ($snapshots->isEmpty()) {
            abort(404);
        }

        $shortUrl = str_replace('https://www.switchscores.com', '', $pageUrl);
        $latest = $snapshots->last();

        $pageTitle = 'Insights: '.$shortUrl;
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::insightsPage($shortUrl))->bindings;

        $bindings['PageUrl']  = $pageUrl;
        $bindings['ShortUrl'] = $shortUrl;

        // Oldest first for the chart, newest first for the table
        $bindings['ChartDataSet'] = $snapshots;
        $bindings['SnapshotList'] = $snapshots->reverse()->values();

        $bindings['LatestSnapshot'] = $latest;
        $bindings['WindowDays']     = $latest->window_days;
        // GSC data lags by two days, so the window ends before the snapshot date
        $bindings['WindowEndDate']  = $latest->snapshot_date->copy()->subDays(2);
        $bindings['ChangeSet']      = $this->buildChanges($snapshots);

        return view('staff.insights.page', $bindings);
    }

    /**
     * Compare the latest snapshot against earlier ones. Snapshots are counted,
     * not dated, as the cron can miss days - so the comparison date is shown
     * alongside each row rather than assumed.
     */
    private function buildChanges(Collection $snapshots): array
    {
        $latest = $snapshots->last();
        $count = $snapshots->count();
        $changes = [];

        foreach ([7, 28] as $snapshotsBack) {
            $index = $count - 1 - $snapshotsBack;

            if ($index < 0) continue;

            $previous = $snapshots->get($index);

            $changes[] = [
                'label'         => $snapshotsBack.' snapshots ago',
                'snapshot_date' => $previous->snapshot_date,
                'impressions'   => $latest->impressions - $previous->impressions,
                'clicks'        => $latest->clicks - $previous->clicks,
                'avg_position'  => ($latest->avg_position !== null && $previous->avg_position !== null)
                    ? round($latest->avg_position - $previous->avg_position, 2)
                    : null,
            ];
        }

        return $changes;
    }
}
