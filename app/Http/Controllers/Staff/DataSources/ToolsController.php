<?php

namespace App\Http\Controllers\Staff\DataSources;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;
use App\Traits\StaffView;

class ToolsController extends Controller
{
    use SwitchServices;
    use StaffView;

    public function nintendoCoUkImportParseLink()
    {
        $pageTitle = 'Nintendo.co.uk API - Import/Parse/Link';
        $bindings = $this->getBindingsDataSourcesSubpage($pageTitle);

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
        $bindings = $this->getBindingsDataSourcesSubpage($pageTitle);

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
        $bindings = $this->getBindingsDataSourcesSubpage($pageTitle);

        if (request()->post()) {
            \Artisan::call('DSNintendoCoUkDownloadImages', []);
            return view('staff.data-sources.tools.process-generic', $bindings);
        } else {
            return view('staff.data-sources.tools.nintendoCoUk.downloadImages-landing', $bindings);
        }
    }

    public function wikipediaImportParseLink()
    {
        $pageTitle = 'Wikipedia - Import/Parse/Link';
        $bindings = $this->getBindingsDataSourcesSubpage($pageTitle);

        if (request()->post()) {
            \Artisan::call('DSWikipediaImportParseLink', []);
            return view('staff.data-sources.tools.process-generic', $bindings);
        } else {
            return view('staff.data-sources.tools.wikipedia.importParseLink-landing', $bindings);
        }
    }

    public function wikipediaUpdateGames()
    {
        $pageTitle = 'Wikipedia - Update games';
        $bindings = $this->getBindingsDataSourcesSubpage($pageTitle);

        if (request()->post()) {
            \Artisan::call('DSWikipediaUpdateGames', []);
            return view('staff.data-sources.tools.process-generic', $bindings);
        } else {
            return view('staff.data-sources.tools.wikipedia.updateGames-landing', $bindings);
        }
    }

}
