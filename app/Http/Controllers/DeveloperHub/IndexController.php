<?php

namespace App\Http\Controllers\DeveloperHub;

use Illuminate\Routing\Controller as Controller;

class IndexController extends Controller
{
    public function show()
    {
        $bindings = [];

        $pageTitle = 'Developer hub';

        $bindings['TopTitle'] = $pageTitle;
        $bindings['PageTitle'] = $pageTitle;

        return view('developer-hub.index', $bindings);
    }
}
