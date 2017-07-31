<?php

namespace App\Http\Controllers;

class WelcomeController extends BaseController
{
    public function show()
    {
        $bindings = array();

        $bindings['TopTitle'] = 'Welcome';
        $bindings['PageTitle'] = 'World of Switch - Homepage';

        return view('welcome', $bindings);
    }
}
