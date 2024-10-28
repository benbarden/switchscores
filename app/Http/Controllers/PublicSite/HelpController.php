<?php

namespace App\Http\Controllers\PublicSite;

use App\Domain\ViewBreadcrumbs\MainSite as Breadcrumbs;
use App\Traits\SwitchServices;
use Illuminate\Routing\Controller as Controller;

class HelpController extends Controller
{
    use SwitchServices;

    public function __construct(
        private Breadcrumbs $viewBreadcrumbs
    )
    {
    }

    public function landing()
    {
        $bindings = [];

        $pageTitle = 'Help';
        $bindings['crumbNav'] = $this->viewBreadcrumbs->topLevelPage($pageTitle);

        $bindings['TopTitle'] = $pageTitle;
        $bindings['PageTitle'] = $pageTitle;

        return view('public.help.landing', $bindings);
    }

    public function lowQualityFilter()
    {
        $bindings = [];

        $pageTitle = 'Low quality filter';
        $bindings['crumbNav'] = $this->viewBreadcrumbs->helpSubpage($pageTitle);

        $bindings['TopTitle'] = $pageTitle;
        $bindings['PageTitle'] = $pageTitle;

        return view('public.help.low-quality-filter', $bindings);
    }
}
