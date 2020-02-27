<?php

namespace App\Http\Controllers\Staff\DataSources;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;

class DashboardController extends Controller
{
    use SwitchServices;

    public function show()
    {
        $pageTitle = 'Data sources dashboard';

        $serviceDataSource = $this->getServiceDataSource();

        $bindings = [];

        $bindings['TopTitle'] = $pageTitle;
        $bindings['PageTitle'] = $pageTitle;

        $bindings['DataSources'] = $serviceDataSource->getAll();

        return view('staff.data-sources.dashboard', $bindings);
    }
}
