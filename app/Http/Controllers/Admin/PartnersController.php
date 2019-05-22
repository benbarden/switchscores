<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller as Controller;

use App\Services\ServiceContainer;

class PartnersController extends Controller
{
    public function landing()
    {
        $bindings = [];
        $bindings['TopTitle'] = 'Partners';
        $bindings['PageTitle'] = 'Partners';

        return view('admin.partners.landing', $bindings);
    }
}