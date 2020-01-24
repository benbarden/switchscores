<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as Controller;

class PrivacyController extends Controller
{
    public function show()
    {
        $bindings = [];

        $bindings['TopTitle'] = 'Privacy';
        $bindings['PageTitle'] = 'Privacy policy';

        return view('privacy', $bindings);
    }
}
