<?php

namespace App\Http\Controllers;

use Carbon\Carbon;

class AboutController extends BaseController
{
    public function show()
    {
        $bindings = array();

        $bindings['TopTitle'] = 'About';
        $bindings['PageTitle'] = 'About World of Switch';

        return view('about', $bindings);
    }
}
