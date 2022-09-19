<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as Controller;

use App\Domain\ViewBreadcrumbs\MainSite as Breadcrumbs;

class PartnersController extends Controller
{
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

        $bindings['crumbNav'] = $this->viewBreadcrumbs->topLevelPage('Partners');

        $bindings['TopTitle'] = 'Partners';
        $bindings['PageTitle'] = 'Partners';

        return view('partners.landing', $bindings);
    }

    public function guidesShow($guideTitle)
    {
        $bindings = [];

        $guide = [];
        $guideView = '';

        switch ($guideTitle) {
            case 'new-review-site-welcome':
                $guide['title'] = 'New review site welcome guide';
                $bindings['crumbNav'] = $this->viewBreadcrumbs->partnersSubpage($guide['title']);
                $guideView = 'partners.guides.newReviewSiteWelcome';
                break;
            default:
                abort(404);
        }

        $bindings['TopTitle'] = $guide['title'];
        $bindings['PageTitle'] = $guide['title'];

        return view($guideView, $bindings);
    }
}
