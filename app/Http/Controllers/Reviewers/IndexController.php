<?php

namespace App\Http\Controllers\Reviewers;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;
use App\Traits\AuthUser;

class IndexController extends Controller
{
    use SwitchServices;
    use AuthUser;

    public function show()
    {
        $servicePartner = $this->getServicePartner();
        $serviceReviewLink = $this->getServiceReviewLink();

        $bindings = [];

        $userId = $this->getAuthId();

        $authUser = $this->getValidUser($this->getServiceUser());

        $partnerId = $authUser->partner_id;

        $partnerData = $servicePartner->find($partnerId);

        // These shouldn't be possible but it saves problems later on
        if (!$partnerData) abort(400);
        if (!$partnerData->isReviewSite()) abort(500);

        $bindings['PartnerData'] = $partnerData;

        $pageTitle = 'Reviewers dashboard: '.$partnerData->name;

        // Review stats (for infobox)
        $reviewStats = $serviceReviewLink->getSiteReviewStats($partnerId);
        $bindings['ReviewAvg'] = round($reviewStats[0]->ReviewAvg, 2);

        // Recent reviews
        $bindings['SiteReviewsLatest'] = $serviceReviewLink->getLatestBySite($partnerId, 5);

        // Score distribution
        $reviewScoreDistribution = $serviceReviewLink->getSiteScoreDistribution($partnerId);

        $mostUsedScore = ['topScore' => 0, 'topScoreCount' => 0];
        if ($reviewScoreDistribution) {
            foreach ($reviewScoreDistribution as $scoreKey => $scoreVal) {
                if ($scoreVal > $mostUsedScore['topScoreCount']) {
                    $mostUsedScore = ['topScore' => $scoreKey, 'topScoreCount' => $scoreVal];
                }
            }
        }
        $bindings['ScoreDistribution'] = $reviewScoreDistribution;
        $bindings['MostUsedScore'] = $mostUsedScore;

        $bindings['TopTitle'] = $pageTitle;
        $bindings['PageTitle'] = $pageTitle;

        return view('reviewers.index', $bindings);
    }
}
