<?php

namespace App\Http\Controllers\Owner;

use Illuminate\Routing\Controller as Controller;

use App\Domain\View\Breadcrumbs\StaffBreadcrumbs;
use App\Domain\View\PageBuilders\StaffPageBuilder;

use App\Domain\ActivityLog\Repository as ActivityLogRepository;

class ActivityLogController extends Controller
{
    public function __construct(
        private StaffPageBuilder $pageBuilder,
        private ActivityLogRepository $repoActivityLog,
    )
    {
    }

    public function show()
    {
        $pageTitle = 'Activity log';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::staffGenericTopLevel($pageTitle))->bindings;

        $activityLog = $this->repoActivityLog->getAll();
        $bindings['ActivityLog'] = $activityLog;

        return view('owner.activity-log.index', $bindings);
    }
}
