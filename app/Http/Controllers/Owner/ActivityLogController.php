<?php

namespace App\Http\Controllers\Owner;

use Illuminate\Routing\Controller as Controller;

use App\Domain\ActivityLog\Repository as ActivityLogRepository;

class ActivityLogController extends Controller
{
    public function __construct(
        private ActivityLogRepository $repoActivityLog
    )
    {
    }

    public function show()
    {
        $pageTitle = 'Dashboard';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->topLevelPage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $activityLog = $this->repoActivityLog->getAll();
        $bindings['ActivityLog'] = $activityLog;

        return view('owner.activity-log.index', $bindings);
    }
}
