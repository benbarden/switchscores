<?php

namespace App\Http\Controllers\Staff\Partners;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;

class DataCleanupController extends Controller
{
    use SwitchServices;

    public function legacyPartnerMultiple()
    {
        $serviceGame = $this->getServiceGame();

        $pageTitle = 'Legacy partners with multiple records';

        $bindings = [];

        $bindings['PageTitle'] = $pageTitle;
        $bindings['TopTitle'] = $pageTitle.' - Partners - Staff';

        $bindings['LegacyDevMultipleList'] = $serviceGame->getOldDevelopersMultiple();
        $bindings['LegacyPubMultipleList'] = $serviceGame->getOldPublishersMultiple();

        return view('staff.partners.data-cleanup.legacy-partner-multiple', $bindings);
    }
}
