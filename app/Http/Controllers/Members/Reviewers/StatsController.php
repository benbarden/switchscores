<?php

namespace App\Http\Controllers\Members\Reviewers;

use Illuminate\Routing\Controller as Controller;

use App\Domain\View\Breadcrumbs\MembersBreadcrumbs;
use App\Domain\View\PageBuilders\MembersPageBuilder;

use App\Domain\ReviewLink\Stats as ReviewLinkStatsRepository;
use App\Domain\ReviewSite\Repository as ReviewSiteRepository;

class StatsController extends Controller
{
    public function __construct(
        private MembersPageBuilder $pageBuilder,
        private ReviewSiteRepository $repoReviewSite,
        private ReviewLinkStatsRepository $repoReviewLinkStats,
    )
    {
    }

    public function landing()
    {
        $pageTitle = 'Stats';
        $bindings = $this->pageBuilder->build($pageTitle, MembersBreadcrumbs::reviewersSubpage($pageTitle))->bindings;

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
