<?php

namespace App\Http\Controllers\User;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;
use App\Traits\MemberView;

class SettingsController extends Controller
{
    use SwitchServices;
    use MemberView;

    public function show()
    {
        $bindings = $this->getBindingsDashboardGenericSubpage('Settings');

        return view('user.settings', $bindings);
    }
}
