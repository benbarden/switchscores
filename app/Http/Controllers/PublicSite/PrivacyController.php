<?php

namespace App\Http\Controllers\PublicSite;

use Illuminate\Routing\Controller as Controller;

use App\Domain\View\Breadcrumbs\PublicBreadcrumbs;
use App\Domain\View\PageBuilders\PublicPageBuilder;

class PrivacyController extends Controller
{
    public function __construct(
        private PublicPageBuilder $pageBuilder,
    )
    {
    }

    public function show()
    {
        $pageTitle = 'Privacy policy';
        $bindings = $this->pageBuilder->build($pageTitle, PublicBreadcrumbs::topLevel($pageTitle))->bindings;

        return view('public.privacy', $bindings);
    }
}
