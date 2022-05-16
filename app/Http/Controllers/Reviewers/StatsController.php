<?php

namespace App\Http\Controllers\Reviewers;

use Illuminate\Routing\Controller as Controller;

use App\Domain\ReviewSite\Repository as ReviewSiteRepository;

use App\Traits\SwitchServices;
use App\Traits\AuthUser;

class StatsController extends Controller
{
    use SwitchServices;
    use AuthUser;

    protected $repoReviewSite;

    public function __construct(
        ReviewSiteRepository $repoReviewSite
    )
    {
        $this->repoReviewSite = $repoReviewSite;
    }

    public function landing()
    {
        $serviceReviewLink = $this->getServiceReviewLink();

        $bindings = [];

        $authUser = $this->getValidUser($this->getServiceUser());

        $partnerId = $authUser->partner_id;

        $reviewSite = $this->repoReviewSite->find($partnerId);

        // These shouldn't be possible but it saves problems later on
        if (!$reviewSite) abort(400);

        $bindings['PartnerData'] = $reviewSite;

        $pageTitle = 'Stats';

        // Review stats (for infobox)
        $reviewStats = $serviceReviewLink->getSiteReviewStats($partnerId);
        $bindings['ReviewAvg'] = round($reviewStats[0]->ReviewAvg, 2);

        // Score distribution
        $bindings['ScoreDistribution'] = $serviceReviewLink->getSiteScoreDistribution($partnerId);

        $bindings['TopTitle'] = $pageTitle;
        $bindings['PageTitle'] = $pageTitle;

        return view('reviewers.stats.landing', $bindings);
    }
}
