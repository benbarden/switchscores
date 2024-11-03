<?php

namespace App\Http\Controllers\Reviewers;

use Illuminate\Routing\Controller as Controller;

use App\Domain\ReviewSite\Repository as ReviewSiteRepository;

use App\Traits\SwitchServices;

class StatsController extends Controller
{
    use SwitchServices;

    public function __construct(
        private ReviewSiteRepository $repoReviewSite
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

        // Review stats (for infobox)
        $reviewStats = $this->getServiceReviewLink()->getSiteReviewStats($partnerId);
        $bindings['ReviewAvg'] = round($reviewStats[0]->ReviewAvg, 2);

        // Score distribution
        $bindings['ScoreDistribution'] = $this->getServiceReviewLink()->getSiteScoreDistribution($partnerId);

        return view('reviewers.stats.landing', $bindings);
    }
}
