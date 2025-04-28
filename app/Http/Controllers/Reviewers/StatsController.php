<?php

namespace App\Http\Controllers\Reviewers;

use Illuminate\Routing\Controller as Controller;

use App\Domain\ReviewSite\Repository as ReviewSiteRepository;
use App\Domain\ReviewLink\Stats as ReviewLinkStatsRepository;

class StatsController extends Controller
{
    public function __construct(
        private ReviewSiteRepository $repoReviewSite,
        private ReviewLinkStatsRepository $repoReviewLinkStats,
    )
    {
    }

    public function landing()
    {
        $pageTitle = 'Stats';
        $breadcrumbs = resolve('View/Breadcrumbs/Member')->reviewersSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Member')->setBreadcrumbs($breadcrumbs)->generateMember($pageTitle);

        $currentUser = resolve('User/Repository')->currentUser();

        $partnerId = $currentUser->partner_id;

        $reviewSite = $this->repoReviewSite->find($partnerId);

        // These shouldn't be possible but it saves problems later on
        if (!$reviewSite) abort(400);

        $bindings['PartnerData'] = $reviewSite;

        // Score distribution
        $bindings['ScoreDistribution'] = $this->repoReviewLinkStats->scoreDistributionBySite($partnerId);

        return view('reviewers.stats.landing', $bindings);
    }
}
