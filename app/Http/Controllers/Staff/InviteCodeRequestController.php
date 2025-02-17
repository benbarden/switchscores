<?php

namespace App\Http\Controllers\Staff;

use Illuminate\Routing\Controller as Controller;

use App\Domain\InviteCodeRequest\Repository as InviteCodeRequestRepository;

class InviteCodeRequestController extends Controller
{
    public function __construct(
        private InviteCodeRequestRepository $repoInviteCodeRequest,
    )
    {
    }

    public function showList()
    {
        $pageTitle = 'Invite code requests';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->topLevelPage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['InviteCodeRequestList'] = $this->repoInviteCodeRequest->getAll();

        return view('staff.invite-code-request-list', $bindings);
    }

}