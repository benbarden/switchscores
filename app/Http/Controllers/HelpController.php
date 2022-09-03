<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as Controller;

use App\Domain\ViewBreadcrumbs\MainSite as Breadcrumbs;

use App\Traits\SwitchServices;

class HelpController extends Controller
{
    use SwitchServices;

    protected $viewBreadcrumbs;

    public function __construct(
        Breadcrumbs $viewBreadcrumbs
    )
    {
        $this->viewBreadcrumbs = $viewBreadcrumbs;
    }

    public function landing()
    {
        $bindings = [];

        $pageTitle = 'Help';
        $bindings['crumbNav'] = $this->viewBreadcrumbs->topLevelPage($pageTitle);

        $bindings['TopTitle'] = $pageTitle;
        $bindings['PageTitle'] = $pageTitle;

        return view('help.landing', $bindings);
    }

    public function lowQualityFilter()
    {
        $bindings = [];

        $pageTitle = 'Low quality filter';
        $bindings['crumbNav'] = $this->viewBreadcrumbs->helpSubpage($pageTitle);

        $bindings['TopTitle'] = $pageTitle;
        $bindings['PageTitle'] = $pageTitle;

        return view('help.low-quality-filter', $bindings);
    }
}
