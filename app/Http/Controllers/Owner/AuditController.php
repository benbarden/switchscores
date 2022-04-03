<?php

namespace App\Http\Controllers\Owner;

use Illuminate\Routing\Controller as Controller;

use App\Domain\Audit\Repository as AuditRepository;

use App\Domain\ViewBreadcrumbs\Staff as Breadcrumbs;
use App\Domain\ViewBindings\Staff as Bindings;

use App\Traits\SwitchServices;

class AuditController extends Controller
{
    use SwitchServices;

    protected $repoAudit;
    protected $viewBreadcrumbs;
    protected $viewBindings;

    public function __construct(
        AuditRepository $repoAudit,
        Bindings $viewBindings,
        Breadcrumbs $viewBreadcrumbs
    )
    {
        $this->repoAudit = $repoAudit;
        $this->viewBreadcrumbs = $viewBreadcrumbs;
        $this->viewBindings = $viewBindings;
    }

    public function index()
    {
        $breadcrumbs = $this->viewBreadcrumbs->topLevelPage('Audit');

        $bindings = $this->viewBindings->setBreadcrumbs($breadcrumbs)->generateStaff('Audit');

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
            case 'partners':
                $pageTitle = 'Audit report: Partners';
                $itemList = $this->repoAudit->getPartner(250);
                break;
            default:
                abort(404);
        }

        $breadcrumbs = $this->viewBreadcrumbs->auditSubpage($pageTitle);

        $bindings = $this->viewBindings->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['ItemList'] = $itemList;

        return view('owner.audit.report', $bindings);
    }
}
