<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;

class DashboardsController extends Controller
{
    use SwitchServices;

    public function feedItemsLanding()
    {
        $bindings = [];

        $bindings['TopTitle'] = 'Feed items';
        $bindings['PageTitle'] = 'Feed items';

        return view('admin.feed-items.landing', $bindings);
    }

    public function index()
    {
        return redirect('/staff');
    }
}
