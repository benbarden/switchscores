<?php

namespace App\Http\Controllers\PublicSite;

use App\Domain\ViewBreadcrumbs\MainSite as Breadcrumbs;
use Illuminate\Routing\Controller as Controller;

class PartnersController extends Controller
{
    public function __construct(
        private Breadcrumbs $viewBreadcrumbs
    )
    {
    }

    public function landing()
    {
        $bindings = [];

        $bindings['crumbNav'] = $this->viewBreadcrumbs->topLevelPage('Partners');

        $bindings['TopTitle'] = 'Partners';
        $bindings['PageTitle'] = 'Partners';

        return view('public.partners.landing', $bindings);
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
                $guideView = 'public.partners.guides.newReviewSiteWelcome';
                break;
            default:
                abort(404);
        }

        $bindings['TopTitle'] = $guide['title'];
        $bindings['PageTitle'] = $guide['title'];

        return view($guideView, $bindings);
    }
}
