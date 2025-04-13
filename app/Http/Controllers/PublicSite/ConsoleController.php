<?php

namespace App\Http\Controllers\PublicSite;

use Illuminate\Routing\Controller as Controller;

use App\Domain\ViewBreadcrumbs\MainSite as Breadcrumbs;


class ConsoleController extends Controller
{
    public function __construct(
        private Breadcrumbs $viewBreadcrumbs
    )
    {
    }

    public function landingSwitch2()
    {
        $bindings = [];

        $bindings['TopTitle'] = 'Nintendo Switch 2';
        $bindings['PageTitle'] = 'Nintendo Switch 2';
        $bindings['crumbNav'] = $this->viewBreadcrumbs->topLevelPage('Switch 2');

        return view('public.console.landing-switch-2', $bindings);
    }
}