<?php

namespace App\Http\Controllers\User;

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
        $serviceCollection = $this->getServiceUserGamesCollection();
        $serviceGameDeveloper = $this->getServiceGameDeveloper();
        $serviceGamePublisher = $this->getServiceGamePublisher();

        $bindings = [];

        $siteRole = 'member'; // default

        $userId = $this->getAuthId();

        $authUser = $this->getValidUser($this->getServiceUser());

        $onPageTitle = 'Member dashboard';

        $bindings['CollectionStats'] = $serviceCollection->getStats($userId);

        if ($authUser->isOwner()) {
            $partnerIdOverride = \Request::get('partnerOverride');
            if ($partnerIdOverride == 'xxx') {
                $partnerId = null;
            } elseif ($partnerIdOverride) {
                $partnerId = $partnerIdOverride;
            } else {
                $partnerId = $authUser->partner_id;
            }
        } else {
            $partnerId = $authUser->partner_id;
        }

        if ($partnerId) {

            $partnerData = $servicePartner->find($partnerId);

            if ($partnerData) {

                $bindings['PartnerData'] = $partnerData;

                if ($partnerData->isReviewSite()) {

                    $siteRole = 'review-partner';
                    $onPageTitle = 'Members dashboard: '.$partnerData->name;

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

                } elseif ($partnerData->isGamesCompany()) {

                    $siteRole = 'games-company';

                    $onPageTitle = 'Games company dashboard: '.$partnerData->name;

                    // Recent games
                    $gameDevList = $serviceGameDeveloper->getGamesByDeveloper($partnerId, false, 5);
                    $gamePubList = $serviceGamePublisher->getGamesByPublisher($partnerId, false, 5);
                    $bindings['GameDevList'] = $gameDevList;
                    $bindings['GamePubList'] = $gamePubList;

                }

            }

        }

        $bindings['TopTitle'] = $onPageTitle;
        $bindings['PageTitle'] = $onPageTitle;
        $bindings['SiteRole'] = $siteRole;

        return view('user.index', $bindings);
    }
}
