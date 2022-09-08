<?php

namespace App\Http\Controllers\Staff\Stats;

use Illuminate\Routing\Controller as Controller;

use App\Domain\ReviewSite\Repository as ReviewSiteRepository;

use App\Traits\SwitchServices;

class ReviewLinkController extends Controller
{
    use SwitchServices;

    private $repoReviewSite;

    public function __construct(
        ReviewSiteRepository $repoReviewSite
    )
    {
        $this->repoReviewSite = $repoReviewSite;
    }

    public function show($partnerId)
    {
        $reviewSite = $this->repoReviewSite->find($partnerId);
        if (!$reviewSite) abort(404);

        $pageTitle = 'Review link stats: '.$reviewSite->name;
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->reviewsReviewSitesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $chartDataSet = $this->getServiceReviewLink()->getMonthlyReviewsBySite($partnerId);
        $bindings['ChartDataSet'] = $chartDataSet;
        $bindings['ChartColourGroups'] = ceil(count($chartDataSet) / 6);

        return view('staff.stats.reviewLink', $bindings);
    }
}
