<?php

namespace App\Http\Controllers\Staff;

use Illuminate\Routing\Controller as Controller;

use App\Domain\View\Breadcrumbs\StaffBreadcrumbs;
use App\Domain\View\PageBuilders\StaffPageBuilder;

use App\Domain\InviteCodeRequest\Repository as InviteCodeRequestRepository;

class InviteCodeRequestController extends Controller
{
    public function __construct(
        private StaffPageBuilder $pageBuilder,
        private InviteCodeRequestRepository $repoInviteCodeRequest,
    )
    {
    }

    public function showList()
    {
        $pageTitle = 'Invite code requests';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::staffGenericTopLevel($pageTitle))->bindings;

        $bindings['InviteCodeRequestListActive'] = $this->repoInviteCodeRequest->getActive();
        $bindings['InviteCodeRequestListSpam'] = $this->repoInviteCodeRequest->getSpam();

        return view('staff.invite-code-request-list', $bindings);
    }

}