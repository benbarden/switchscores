<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SiteRequestData;
use App\Traits\WosServices;

class AboutController extends Controller
{
    use SiteRequestData;
    use WosServices;

    public function show()
    {
        $bindings = [];

        $bindings['TopTitle'] = 'About';
        $bindings['PageTitle'] = 'About Switch Scores';

        return view('about', $bindings);
    }
}
