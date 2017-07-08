<?php

namespace App\Http\Controllers\Admin;

class IndexController extends \App\Http\Controllers\BaseController
{
    public function show()
    {
        $bindings = array();

        $bindings['TopTitle'] = 'Admin - Index';

        return view('admin.index', $bindings);
    }
}
