<?php

namespace App\Http\Controllers\Staff\Stats;

use Illuminate\Routing\Controller as Controller;

use App\Domain\View\Breadcrumbs\StaffBreadcrumbs;
use App\Domain\View\PageBuilders\StaffPageBuilder;

use App\Domain\ReviewSite\Repository as ReviewSiteRepository;
use App\Domain\ReviewLink\Stats as ReviewSiteStats;

class ReviewLinkController extends Controller
{
    public function __construct(
        private StaffPageBuilder $pageBuilder,
        private ReviewSiteRepository $repoReviewSite,
        private ReviewSiteStats $statsReviewSite,
    )
    {
    }

    public function show($siteId)
    {
        $reviewSite = $this->repoReviewSite->find($siteId);
        if (!$reviewSite) abort(404);

        $pageTitle = 'Review link stats: '.$reviewSite->name;
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::reviewsReviewSitesSubpage($pageTitle))->bindings;

        $chartDataSet = $this->statsReviewSite->monthlyCountBySite($siteId);
        $bindings['ChartDataSet'] = $chartDataSet;
        $bindings['ChartColourGroups'] = ceil(count($chartDataSet) / 6);

        return view('staff.stats.reviewLink', $bindings);
    }
}
