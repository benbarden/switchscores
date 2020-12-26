<?php

namespace App\Http\Controllers\Staff\Stats;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;
use App\Traits\StaffView;

class DashboardController extends Controller
{
    use SwitchServices;
    use StaffView;

    public function show()
    {
        $bindings = $this->getBindingsDashboardGenericSubpage('Stats dashboard');

        $bindings['TotalGameCount'] = $this->getServiceGame()->getCount();
        $bindings['ReleasedGameCount'] = $this->getServiceGameReleaseDate()->countReleased();
        $bindings['UpcomingGameCount'] = $this->getServiceGameReleaseDate()->countUpcoming();

        return view('staff.stats.dashboard', $bindings);
    }
}
