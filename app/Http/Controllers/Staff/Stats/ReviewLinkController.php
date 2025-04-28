<?php

namespace App\Http\Controllers\Staff\Stats;

use Illuminate\Routing\Controller as Controller;

use App\Domain\ReviewSite\Repository as ReviewSiteRepository;
use App\Domain\ReviewLink\Stats as ReviewSiteStats;

use App\Traits\SwitchServices;

class ReviewLinkController extends Controller
{
    use SwitchServices;

    public function __construct(
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
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->reviewsReviewSitesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $chartDataSet = $this->statsReviewSite->monthlyCountBySite($siteId);
        $bindings['ChartDataSet'] = $chartDataSet;
        $bindings['ChartColourGroups'] = ceil(count($chartDataSet) / 6);

        return view('staff.stats.reviewLink', $bindings);
    }
}
