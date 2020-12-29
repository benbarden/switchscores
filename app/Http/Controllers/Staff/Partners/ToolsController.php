<?php

namespace App\Http\Controllers\Staff\Partners;

use App\Traits\StaffView;
use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;

class ToolsController extends Controller
{
    use SwitchServices;
    use StaffView;

    public function partnerUpdateFields()
    {
        $bindings = $this->getBindingsPartnersSubpage('Partner Update Fields');

        if (request()->post()) {
            \Artisan::call('PartnerUpdateFields', []);
            return view('staff.partners.tools.partnerUpdateFields.process', $bindings);
        } else {
            return view('staff.partners.tools.partnerUpdateFields.landing', $bindings);
        }
    }
}
