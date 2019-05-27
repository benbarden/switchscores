<?php

namespace App\Http\Controllers\User;

use Illuminate\Routing\Controller as Controller;
use App\Services\ServiceContainer;
use Auth;

class IndexController extends Controller
{
    public function show()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $servicePartner = $serviceContainer->getPartnerService();
        $serviceReviewLink = $serviceContainer->getReviewLinkService();
        $serviceCollection = $serviceContainer->getUserGamesCollectionService();

        $bindings = [];

        $bindings['UserRegion'] = Auth::user()->region;

        $siteRole = 'member'; // default

        $userId = Auth::id();

        $authUser = Auth::user();

        $onPageTitle = 'Member dashboard';

        $bindings['CollectionStats'] = $serviceCollection->getStats($userId);

        $siteId = $authUser->partner_id;

        if ($siteId) {

            $partnerData = $servicePartner->find($siteId);

            if ($partnerData) {

                $bindings['PartnerData'] = $partnerData;

                if ($partnerData->isReviewSite()) {

                    $siteRole = 'review-partner';
                    $onPageTitle = 'Review partner dashboard: '.$partnerData->name;

                    // Review stats (for infobox)
                    $reviewStats = $serviceReviewLink->getSiteReviewStats($siteId);
                    $bindings['ReviewCount'] = $reviewStats[0]->ReviewCount;
                    $bindings['ReviewAvg'] = round($reviewStats[0]->ReviewAvg, 2);

                    // Recent reviews
                    $bindings['SiteReviewsLatest'] = $serviceReviewLink->getLatestBySite($siteId, 5);

                    // Score distribution
                    $reviewScoreDistribution = $serviceReviewLink->getSiteScoreDistribution($siteId);

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

                }

            }

        }

        $bindings['TopTitle'] = $onPageTitle;
        $bindings['PageTitle'] = $onPageTitle;
        $bindings['SiteRole'] = $siteRole;

        return view('user.index', $bindings);
    }
}
