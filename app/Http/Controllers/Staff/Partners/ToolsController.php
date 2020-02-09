<?php

namespace App\Http\Controllers\Staff\Partners;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;

class ToolsController extends Controller
{
    use SwitchServices;

    public function partnerUpdateFields()
    {
        $pageTitle = 'Partner Update Fields';
        $topTitle = $pageTitle.' - Tools - Partners - Staff';

        $bindings = [];
        $bindings['TopTitle'] = $topTitle;
        $bindings['PageTitle'] = $pageTitle;

        if (request()->post()) {
            \Artisan::call('PartnerUpdateFields', []);
            return view('staff.partners.tools.partnerUpdateFields.process', $bindings);
        } else {
            return view('staff.partners.tools.partnerUpdateFields.landing', $bindings);
        }
    }

    public function partnerMigrateGameDevsPubs()
    {
        $pageTitle = 'Partner Migrate Game Devs/Pubs';
        $topTitle = $pageTitle.' - Tools - Partners - Staff';

        $bindings = [];
        $bindings['TopTitle'] = $topTitle;
        $bindings['PageTitle'] = $pageTitle;

        if (request()->post()) {
            \Artisan::call('PartnerMigrateGameDevsPubs', []);
            return view('staff.partners.tools.partnerMigrateGameDevsPubs.process', $bindings);
        } else {
            return view('staff.partners.tools.partnerMigrateGameDevsPubs.landing', $bindings);
        }
    }
}
