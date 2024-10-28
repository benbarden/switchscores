<?php

namespace App\Http\Controllers\PublicSite;

use App\Domain\ViewBreadcrumbs\MainSite as Breadcrumbs;
use Illuminate\Routing\Controller as Controller;

class PrivacyController extends Controller
{
    public function __construct(
        private Breadcrumbs $viewBreadcrumbs
    )
    {
    }

    public function show()
    {
        $bindings = [];

        $bindings['crumbNav'] = $this->viewBreadcrumbs->topLevelPage('Privacy');

        $bindings['TopTitle'] = 'Privacy';
        $bindings['PageTitle'] = 'Privacy policy';

        return view('public.privacy', $bindings);
    }
}
