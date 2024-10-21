<?php

namespace App\Http\Controllers\Staff\Partners;

use Illuminate\Routing\Controller as Controller;

class ToolsController extends Controller
{
    public function partnerUpdateFields()
    {
        $pageTitle = 'Partner Update Fields';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->gamesCompaniesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        if (request()->post()) {
            \Artisan::call('ReviewSiteUpdateStats', []);
            return view('staff.partners.tools.partnerUpdateFields.process', $bindings);
        } else {
            return view('staff.partners.tools.partnerUpdateFields.landing', $bindings);
        }
    }
}
