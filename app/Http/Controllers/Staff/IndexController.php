<?php

namespace App\Http\Controllers\Staff;

use Illuminate\Routing\Controller as Controller;

class IndexController extends Controller
{
    public function index()
    {
        $pageTitle = 'Staff index';
        $bindings['TopTitle'] = $pageTitle;
        $bindings['PageTitle'] = $pageTitle;

        return view('staff.index', $bindings);
    }
}
