<?php

namespace App\Http\Controllers\PublicSite;

use Illuminate\Routing\Controller as Controller;

use App\Domain\View\Breadcrumbs\PublicBreadcrumbs;
use App\Domain\View\PageBuilders\PublicPageBuilder;

class PartnersController extends Controller
{
    public function __construct(
        private PublicPageBuilder $pageBuilder,
    )
    {
    }

    public function landing()
    {
        $pageTitle = 'Partners';
        $bindings = $this->pageBuilder->build($pageTitle, PublicBreadcrumbs::topLevel($pageTitle))->bindings;

        return view('public.partners.landing', $bindings);
    }

    public function guidesShow($guideTitle)
    {
        switch ($guideTitle) {
            case 'new-review-site-welcome':
                $pageTitle = 'New review site welcome guide';
                $guideView = 'public.partners.guides.newReviewSiteWelcome';
                break;
            default:
                abort(404);
        }

        $bindings = $this->pageBuilder->build($pageTitle, PublicBreadcrumbs::partnersSubpage($pageTitle))->bindings;

        return view($guideView, $bindings);
    }
}
