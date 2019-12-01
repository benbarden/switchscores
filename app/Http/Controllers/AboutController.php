<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as Controller;

use App\Services\ServiceContainer;

class AboutController extends Controller
{
    public function show()
    {
        $bindings = [];

        $bindings['TopTitle'] = 'About';
        $bindings['PageTitle'] = 'About Switch Scores';

        return view('about', $bindings);
    }
}
