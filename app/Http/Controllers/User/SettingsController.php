<?php

namespace App\Http\Controllers\User;

use Illuminate\Routing\Controller as Controller;

class SettingsController extends Controller
{
    public function show()
    {
        $bindings = [];

        $bindings['TopTitle'] = 'Settings';
        $bindings['PageTitle'] = 'Settings';

        return view('user.settings', $bindings);
    }
}
