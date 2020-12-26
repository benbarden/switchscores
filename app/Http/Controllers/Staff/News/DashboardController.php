<?php

namespace App\Http\Controllers\Staff\News;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;
use App\Traits\StaffView;

class DashboardController extends Controller
{
    use SwitchServices;
    use StaffView;

    public function show()
    {
        $bindings = $this->getBindingsDashboardGenericSubpage('News dashboard');

        return view('staff.news.dashboard', $bindings);
    }
}
