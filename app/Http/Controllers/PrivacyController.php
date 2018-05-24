<?php

namespace App\Http\Controllers;

use Carbon\Carbon;

class PrivacyController extends BaseController
{
    public function show()
    {
        $bindings = array();

        $bindings['TopTitle'] = 'Privacy';
        $bindings['PageTitle'] = 'Privacy policy';

        return view('privacy', $bindings);
    }
}
