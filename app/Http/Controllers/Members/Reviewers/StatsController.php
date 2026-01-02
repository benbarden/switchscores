<?php

namespace App\Http\Controllers\Members\Reviewers;

use App\Domain\ReviewLink\Stats as ReviewLinkStatsRepository;
use App\Domain\ReviewSite\Repository as ReviewSiteRepository;
use Illuminate\Routing\Controller as Controller;

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

        return view('members.reviewers.stats.landing', $bindings);
    }
}
