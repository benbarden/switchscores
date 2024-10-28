<?php

namespace App\Http\Controllers\Staff\DataQuality;

use Illuminate\Routing\Controller as Controller;

use App\Domain\IntegrityCheck\Repository as IntegrityCheckRepository;

use App\Traits\SwitchServices;

use App\Services\DataQuality\QualityStats;

class DashboardController extends Controller
{
    public function __construct(
        private IntegrityCheckRepository $repoIntegrityCheck
    )
    {
    }

    public function show()
    {
        $pageTitle = 'Data quality dashboard';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->topLevelPage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['IntegrityChecks'] = $this->repoIntegrityCheck->getAll();

        $serviceQualityStats = new QualityStats();
        $bindings['DuplicateReviewsCount'] = count($serviceQualityStats->getDuplicateReviews());

        return view('staff.data-quality.dashboard', $bindings);
    }

    public function duplicateReviews()
    {
        $pageTitle = 'Duplicate reviews';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->dataQualitySubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $serviceQualityStats = new QualityStats();
        $bindings['DuplicateReviews'] = $serviceQualityStats->getDuplicateReviews();

        return view('staff.data-quality.duplicate-reviews', $bindings);
    }
}
