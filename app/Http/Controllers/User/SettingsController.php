<?php

namespace App\Http\Controllers\User;

use Illuminate\Routing\Controller as Controller;
use App\Services\ServiceContainer;
use Auth;

class SettingsController extends Controller
{
    public function show()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $bindings = [];

        $bindings['TopTitle'] = 'Settings';
        $bindings['PageTitle'] = 'Settings';

        $bindings['PanelTitle'] = 'Settings';

        $bindings['UserRegion'] = Auth::user()->region;

        return view('user.settings', $bindings);
    }
}
