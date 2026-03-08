<?php

namespace App\Http\Controllers\Staff;

use Illuminate\Routing\Controller as Controller;

use App\Domain\View\Breadcrumbs\StaffBreadcrumbs;
use App\Domain\View\PageBuilders\StaffPageBuilder;
use App\Domain\User\Repository as UserRepository;

class UnverifiedMembersController extends Controller
{
    public function __construct(
        private StaffPageBuilder $pageBuilder,
        private UserRepository $repoUser
    )
    {
    }

    public function index()
    {
        $pageTitle = 'Unverified members';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::unverifiedMembersDashboard())->bindings;

        $bindings['UnverifiedMembers'] = $this->repoUser->getUnverified();

        return view('staff.unverified-members.index', $bindings);
    }

    public function verify($userId)
    {
        $user = $this->repoUser->find($userId);
        if (!$user) abort(404);

        $this->repoUser->verifyEmail($user);

        return redirect()->route('staff.unverified-members.index')
            ->with('success', 'User ' . $user->email . ' has been verified.');
    }

    public function delete($userId)
    {
        $user = $this->repoUser->find($userId);
        if (!$user) abort(404);

        $email = $user->email;
        $this->repoUser->deleteUser($userId);

        return redirect()->route('staff.unverified-members.index')
            ->with('success', 'User ' . $email . ' has been deleted.');
    }
}
