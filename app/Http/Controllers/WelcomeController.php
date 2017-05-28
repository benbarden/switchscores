<?php

namespace App\Http\Controllers;

class WelcomeController extends BaseController
{
    public function show()
    {
        $bindings = array();

        $bindings['TopTitle'] = 'Welcome';

        return view('welcome', $bindings);
    }
}
