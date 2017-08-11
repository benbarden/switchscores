<?php

namespace App\Http\Controllers\Admin;

class IndexController extends \App\Http\Controllers\BaseController
{
    public function show()
    {
        $bindings = array();

        $bindings['TopTitle'] = 'Admin index';
        $bindings['PanelTitle'] = 'Admin index';

        return view('admin.index', $bindings);
    }
}
