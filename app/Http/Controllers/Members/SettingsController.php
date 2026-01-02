<?php

namespace App\Http\Controllers\Members;

use Illuminate\Routing\Controller as Controller;

class SettingsController extends Controller
{
    public function show()
    {
        $pageTitle = 'Settings';
        $breadcrumbs = resolve('View/Breadcrumbs/Member')->topLevelPage($pageTitle);
        $bindings = resolve('View/Bindings/Member')->setBreadcrumbs($breadcrumbs)->generateMember($pageTitle);

        return view('members.settings', $bindings);
    }
}
