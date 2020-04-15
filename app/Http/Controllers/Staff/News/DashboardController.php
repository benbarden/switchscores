<?php

namespace App\Http\Controllers\Staff\News;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;

class DashboardController extends Controller
{
    use SwitchServices;

    public function show()
    {
        $pageTitle = 'News dashboard';

        $bindings = [];

        $bindings['TopTitle'] = $pageTitle.' - Staff';
        $bindings['PageTitle'] = $pageTitle;

        return view('staff.news.dashboard', $bindings);
    }
}
