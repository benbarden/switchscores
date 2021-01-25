<?php

namespace App\Http\Controllers\Staff\Stats;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;
use App\Traits\StaffView;

class ReviewLinkController extends Controller
{
    use SwitchServices;
    use StaffView;

    public function show($partnerId)
    {
        $partner = $this->getServicePartner()->find($partnerId);

        $bindings = $this->getBindingsReviewSitesSubpage('Review link stats: '.$partner->name);

        $chartDataSet = $this->getServiceReviewLink()->getMonthlyReviewsBySite($partnerId);
        $bindings['ChartDataSet'] = $chartDataSet;
        $bindings['ChartColourGroups'] = ceil(count($chartDataSet) / 6);

        return view('staff.stats.reviewLink', $bindings);
    }
}
