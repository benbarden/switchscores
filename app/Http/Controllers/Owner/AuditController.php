<?php

namespace App\Http\Controllers\Owner;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SiteRequestData;
use App\Traits\WosServices;

class AuditController extends Controller
{
    use SiteRequestData;
    use WosServices;

    public function showReport($reportName)
    {
        $bindings = [];

        switch ($reportName) {
            case 'games':
                $pageTitle = 'Audit report: Games';
                $bindings['ItemList'] = $this->getServiceAudit()->getAll(250);
                break;
            default:
                abort(404);
        }

        $bindings['TopTitle'] = $pageTitle;
        $bindings['PageTitle'] = $pageTitle;
        $bindings['jsInitialSort'] = "[ 0, 'desc']";

        return view('owner.audit.report', $bindings);
    }

    public function index()
    {
        $bindings = [];

        $bindings['TopTitle'] = 'Audit';
        $bindings['PageTitle'] = 'Audit';

        return view('owner.audit.index', $bindings);
    }
}
