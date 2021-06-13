<?php

namespace App\Http\Controllers\Owner;

use Illuminate\Routing\Controller as Controller;

use App\Domain\ViewBreadcrumbs\Staff as Breadcrumbs;

use App\Traits\SwitchServices;
use App\Traits\StaffView;

class AuditController extends Controller
{
    use SwitchServices;
    use StaffView;

    protected $viewBreadcrumbs;

    public function __construct(
        Breadcrumbs $viewBreadcrumbs
    )
    {
        $this->viewBreadcrumbs = $viewBreadcrumbs;
    }

    public function index()
    {
        $bindings = $this->getBindings('Audit');

        $bindings['crumbNav'] = $this->viewBreadcrumbs->topLevelPage('Audit');

        return view('owner.audit.index', $bindings);
    }

    public function showReport($reportName)
    {
        switch ($reportName) {
            case 'all':
                $pageTitle = 'Audit report: All';
                $itemList = $this->getServiceAudit()->getAll(250);
                break;
            case 'games':
                $pageTitle = 'Audit report: Games';
                $itemList = $this->getServiceAudit()->getGame(250);
                break;
            case 'review-links':
                $pageTitle = 'Audit report: Review links';
                $itemList = $this->getServiceAudit()->getReviewLink(250);
                break;
            case 'partners':
                $pageTitle = 'Audit report: Partners';
                $itemList = $this->getServiceAudit()->getPartner(250);
                break;
            default:
                abort(404);
        }

        $bindings = $this->getBindings($pageTitle);
        $bindings['crumbNav'] = $this->viewBreadcrumbs->auditSubpage($pageTitle);
        $bindings['ItemList'] = $itemList;

        return view('owner.audit.report', $bindings);
    }
}
