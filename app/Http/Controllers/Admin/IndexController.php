<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Auth;

class IndexController extends \App\Http\Controllers\BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function show()
    {
        $bindings = array();

        $bindings['TopTitle'] = 'Admin - Index';

        if (Auth::check()) {
            $bindings['LoggedIn'] = 'Yes';
        } else {
            $bindings['LoggedIn'] = 'No';
        }

        return view('admin.index', $bindings);
    }
}
