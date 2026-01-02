<?php

namespace App\Http\Controllers\Members;

use Illuminate\Routing\Controller as Controller;

use App\Domain\View\Breadcrumbs\MembersBreadcrumbs;
use App\Domain\View\PageBuilders\MembersPageBuilder;

class SettingsController extends Controller
{
    public function __construct(
        private MembersPageBuilder $pageBuilder,
    )
    {}

    public function show()
    {
        $pageTitle = 'Settings';
        $bindings = $this->pageBuilder->build($pageTitle, MembersBreadcrumbs::membersGenericTopLevel($pageTitle))->bindings;

        return view('members.settings', $bindings);
    }
}
