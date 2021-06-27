<?php

namespace App\Http\Controllers\Staff\DataQuality;

use Illuminate\Routing\Controller as Controller;

use App\Domain\ViewBreadcrumbs\Staff as Breadcrumbs;
use App\Domain\IntegrityCheck\Repository as IntegrityCheckRepository;

use App\Traits\SwitchServices;
use App\Traits\StaffView;

use App\Services\DataQuality\QualityStats;

class DashboardController extends Controller
{
    use SwitchServices;
    use StaffView;

    protected $viewBreadcrumbs;
    protected $repoIntegrityCheck;

    public function __construct(
        Breadcrumbs $viewBreadcrumbs,
        IntegrityCheckRepository $repoIntegrityCheck
    )
    {
        $this->viewBreadcrumbs = $viewBreadcrumbs;
        $this->repoIntegrityCheck = $repoIntegrityCheck;
    }

    public function show()
    {
        $bindings = $this->getBindings('Data quality dashboard');
        $bindings['crumbNav'] = $this->viewBreadcrumbs->topLevelPage('Data quality dashboard');

        $bindings['IntegrityChecks'] = $this->repoIntegrityCheck->getAll();

        $serviceQualityStats = new QualityStats();
        $bindings['DuplicateReviewsCount'] = count($serviceQualityStats->getDuplicateReviews());

        return view('staff.data-quality.dashboard', $bindings);
    }

    public function duplicateReviews()
    {
        $bindings = $this->getBindings('Duplicate reviews');
        $bindings['crumbNav'] = $this->viewBreadcrumbs->dataQualitySubpage('Duplicate reviews');

        $serviceQualityStats = new QualityStats();
        $bindings['DuplicateReviews'] = $serviceQualityStats->getDuplicateReviews();

        return view('staff.data-quality.duplicate-reviews', $bindings);
    }
}
