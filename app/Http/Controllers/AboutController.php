<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as Controller;

use App\Domain\ViewBreadcrumbs\MainSite as Breadcrumbs;

use App\Traits\SwitchServices;

class AboutController extends Controller
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

        $bindings['crumbNav'] = $this->viewBreadcrumbs->topLevelPage('About');

        $bindings['TopTitle'] = 'About';
        $bindings['PageTitle'] = 'About Switch Scores';

        return view('about.landing', $bindings);
    }

    public function changelog()
    {
        $bindings = [];

        $bindings['crumbNav'] = $this->viewBreadcrumbs->aboutSubpage('Changelog');

        $bindings['TopTitle'] = 'Changelog';
        $bindings['PageTitle'] = 'Changelog';

        return view('about.changelog', $bindings);
    }
}
