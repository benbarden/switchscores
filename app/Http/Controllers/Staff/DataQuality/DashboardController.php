<?php

namespace App\Http\Controllers\Staff\DataQuality;

use Illuminate\Routing\Controller as Controller;

use App\Domain\View\Breadcrumbs\StaffBreadcrumbs;
use App\Domain\View\PageBuilders\StaffPageBuilder;

use App\Domain\IntegrityCheck\Repository as IntegrityCheckRepository;

use App\Services\DataQuality\QualityStats;

class DashboardController extends Controller
{
    public function __construct(
        private StaffPageBuilder $pageBuilder,
        private IntegrityCheckRepository $repoIntegrityCheck
    )
    {
    }

    public function show()
    {
        $pageTitle = 'Data quality dashboard';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::dataQualityDashboard())->bindings;

        $bindings['IntegrityChecks'] = $this->repoIntegrityCheck->getAll();

        $serviceQualityStats = new QualityStats();
        $bindings['DuplicateReviewsCount'] = count($serviceQualityStats->getDuplicateReviews());

        return view('staff.data-quality.dashboard', $bindings);
    }

    public function duplicateReviews()
    {
        $pageTitle = 'Duplicate reviews';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::dataQualitySubpage($pageTitle))->bindings;

        $serviceQualityStats = new QualityStats();
        $bindings['DuplicateReviews'] = $serviceQualityStats->getDuplicateReviews();

        return view('staff.data-quality.duplicate-reviews', $bindings);
    }
}
