<?php

namespace App\Http\Controllers\Staff\DataQuality;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;
use App\Traits\StaffView;

use App\Services\DataQuality\QualityStats;

class DashboardController extends Controller
{
    use SwitchServices;
    use StaffView;

    public function show()
    {
        $bindings = $this->getBindingsDashboardGenericSubpage('Data quality dashboard');

        $serviceQualityStats = new QualityStats();
        $bindings['DuplicateReviewsCount'] = count($serviceQualityStats->getDuplicateReviews());

        return view('staff.data-quality.dashboard', $bindings);
    }

    public function duplicateReviews()
    {
        $bindings = $this->getBindingsDataQualitySubpage('Duplicate reviews');

        $serviceQualityStats = new QualityStats();
        $bindings['DuplicateReviews'] = $serviceQualityStats->getDuplicateReviews();

        return view('staff.data-quality.duplicate-reviews', $bindings);
    }
}
