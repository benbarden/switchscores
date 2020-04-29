<?php

namespace App\Http\Controllers\Reviewers;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;
use App\Traits\AuthUser;

class StatsController extends Controller
{
    use SwitchServices;
    use AuthUser;

    public function landing()
    {
        $servicePartner = $this->getServicePartner();
        $serviceReviewLink = $this->getServiceReviewLink();

        $bindings = [];

        $authUser = $this->getValidUser($this->getServiceUser());

        $partnerId = $authUser->partner_id;

        $partnerData = $servicePartner->find($partnerId);

        // These shouldn't be possible but it saves problems later on
        if (!$partnerData) abort(400);
        if (!$partnerData->isReviewSite()) abort(500);

        $bindings['PartnerData'] = $partnerData;

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
