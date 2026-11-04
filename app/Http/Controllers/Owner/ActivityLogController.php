<?php

namespace App\Http\Controllers\Owner;

use Illuminate\Routing\Controller as Controller;

use App\Domain\View\Breadcrumbs\StaffBreadcrumbs;
use App\Domain\View\PageBuilders\StaffPageBuilder;

use App\Domain\ActivityLog\Repository as ActivityLogRepository;
use App\Models\ActivityLog;

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

        // An unknown type shows everything rather than an empty page
        $eventType = request()->query('type');
        if (!in_array($eventType, ActivityLog::eventTypes(), true)) {
            $eventType = null;
        }

        $bindings['ActivityLog'] = $this->repoActivityLog->getAll($eventType);
        $bindings['EventTypes'] = ActivityLog::eventTypes();
        $bindings['SelectedType'] = $eventType;

        return view('owner.activity-log.index', $bindings);
    }
}
