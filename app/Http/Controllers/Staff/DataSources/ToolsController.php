<?php

namespace App\Http\Controllers\Staff\DataSources;

use Illuminate\Routing\Controller as Controller;

class ToolsController extends Controller
{
    public function nintendoCoUkImportParseLink()
    {
        $pageTitle = 'Nintendo.co.uk API - Import/Parse/Link';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->dataSourcesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        if (request()->post()) {
            \Artisan::call('DSNintendoCoUkImportParseLink', []);
            return view('staff.data-sources.tools.process-generic', $bindings);
        } else {
            return view('staff.data-sources.tools.nintendoCoUk.importParseLink-landing', $bindings);
        }
    }

    public function nintendoCoUkUpdateGames()
    {
        $pageTitle = 'Nintendo.co.uk API - Update games';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->dataSourcesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        if (request()->post()) {
            \Artisan::call('DSNintendoCoUkUpdateGames', []);
            return view('staff.data-sources.tools.process-generic', $bindings);
        } else {
            return view('staff.data-sources.tools.nintendoCoUk.updateGames-landing', $bindings);
        }
    }

    public function nintendoCoUkDownloadImages()
    {
        $pageTitle = 'Nintendo.co.uk API - Download images';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->dataSourcesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        if (request()->post()) {
            \Artisan::call('DSNintendoCoUkDownloadImages', []);
            return view('staff.data-sources.tools.process-generic', $bindings);
        } else {
            return view('staff.data-sources.tools.nintendoCoUk.downloadImages-landing', $bindings);
        }
    }

}
