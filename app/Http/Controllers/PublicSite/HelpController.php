<?php

namespace App\Http\Controllers\PublicSite;

use Illuminate\Routing\Controller as Controller;

use App\Domain\View\Breadcrumbs\PublicBreadcrumbs;
use App\Domain\View\PageBuilders\PublicPageBuilder;

class HelpController extends Controller
{
    public function __construct(
        private PublicPageBuilder $pageBuilder,
    )
    {
    }

    public function landing()
    {
        $pageTitle = 'Help';
        $bindings = $this->pageBuilder->build($pageTitle, PublicBreadcrumbs::topLevel($pageTitle))->bindings;

        return view('public.help.landing', $bindings);
    }

    public function lowQualityFilter()
    {
        $pageTitle = 'Low quality filter';
        $bindings = $this->pageBuilder->build($pageTitle, PublicBreadcrumbs::helpSubpage($pageTitle))->bindings;

        return view('public.help.low-quality-filter', $bindings);
    }
}
