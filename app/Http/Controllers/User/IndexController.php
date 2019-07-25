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

        $regionCode = \Request::get('regionCode');

        $servicePartner = $serviceContainer->getPartnerService();
        $serviceReviewLink = $serviceContainer->getReviewLinkService();
        $serviceCollection = $serviceContainer->getUserGamesCollectionService();
        $serviceGameDeveloper = $serviceContainer->getGameDeveloperService();
        $serviceGamePublisher = $serviceContainer->getGamePublisherService();

        $bindings = [];

        $bindings['UserRegion'] = Auth::user()->region;

        $siteRole = 'member'; // default

        $userId = Auth::id();

        $authUser = Auth::user();

        $onPageTitle = 'Member dashboard';

        $bindings['CollectionStats'] = $serviceCollection->getStats($userId);

        $partnerId = $authUser->partner_id;

        if ($partnerId) {

            $partnerData = $servicePartner->find($partnerId);

            if ($partnerData) {

                $bindings['PartnerData'] = $partnerData;

                if ($partnerData->isReviewSite()) {

                    $siteRole = 'review-partner';
                    $onPageTitle = 'Review partner dashboard: '.$partnerData->name;

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
                    $gameDevList = $serviceGameDeveloper->getGamesByDeveloper($regionCode, $partnerId, false, 5);
                    $gamePubList = $serviceGamePublisher->getGamesByPublisher($regionCode, $partnerId, false, 5);
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
