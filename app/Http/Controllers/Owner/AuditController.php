<?php

namespace App\Http\Controllers\Owner;

use Illuminate\Routing\Controller as Controller;

use App\Domain\View\Breadcrumbs\StaffBreadcrumbs;
use App\Domain\View\PageBuilders\StaffPageBuilder;

use App\Domain\Audit\Repository as AuditRepository;

class AuditController extends Controller
{
    public function __construct(
        private StaffPageBuilder $pageBuilder,
        private AuditRepository $repoAudit,
    )
    {
    }

    public function index()
    {
        $pageTitle = 'Audit';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::auditDashboard())->bindings;

        return view('owner.audit.index', $bindings);
    }

    public function showReport($reportName)
    {
        switch ($reportName) {
            case 'all':
                $pageTitle = 'Audit report: All';
                $itemList = $this->repoAudit->getAll(250);
                break;
            case 'games':
                $pageTitle = 'Audit report: Games';
                $itemList = $this->repoAudit->getGame(250);
                break;
            case 'review-links':
                $pageTitle = 'Audit report: Review links';
                $itemList = $this->repoAudit->getReviewLink(250);
                break;
            case 'games-companies':
                $pageTitle = 'Audit report: Games companies';
                $itemList = $this->repoAudit->getGamesCompany(250);
                break;
            default:
                abort(404);
        }

        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::auditSubpage($pageTitle))->bindings;

        $bindings['ItemList'] = $itemList;

        return view('owner.audit.report', $bindings);
    }
}
