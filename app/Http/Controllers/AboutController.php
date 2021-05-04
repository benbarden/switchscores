<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;

class AboutController extends Controller
{
    use SwitchServices;

    public function landing()
    {
        $bindings = [];

        $bindings['TopTitle'] = 'About';
        $bindings['PageTitle'] = 'About Switch Scores';

        return view('about.landing', $bindings);
    }

    public function changelog()
    {
        $bindings = [];

        $bindings['TopTitle'] = 'Changelog';
        $bindings['PageTitle'] = 'Changelog';

        return view('about.changelog', $bindings);
    }
}
