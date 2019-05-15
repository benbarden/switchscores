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

        $serviceReviewSite = $serviceContainer->getReviewSiteService();
        $serviceReviewLink = $serviceContainer->getReviewLinkService();
        $serviceCollection = $serviceContainer->getUserGamesCollectionService();

        $bindings = [];

        $bindings['UserRegion'] = Auth::user()->region;

        $siteRole = 'member'; // default

        $userId = Auth::id();

        $authUser = Auth::user();

        $onPageTitle = 'Member dashboard';

        $bindings['CollectionStats'] = $serviceCollection->getStats($userId);

        $siteId = $authUser->site_id;
        if ($siteId) {
            $reviewSite = $serviceReviewSite->find($siteId);
            if ($reviewSite) {

                $bindings['ReviewSite'] = $reviewSite;
                $siteRole = 'review-partner';
                $onPageTitle = 'Review partner dashboard: '.$reviewSite->name;

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

            }

        }

        $userDevId = $authUser->developer_id;
        $userPubId = $authUser->publisher_id;

        if ($userDevId != 0 || $userPubId != 0) {

            // this will override review-partner if both are set
            $siteRole = 'developer-publisher';

            if ($userDevId != 0) {
                $onPageTitle = 'Developer dashboard: '.$authUser->developer->name;
            } elseif ($userPubId != 0) {
                $onPageTitle = 'Publisher dashboard: '.$authUser->publisher->name;
            }
        }

        $bindings['TopTitle'] = $onPageTitle;
        $bindings['PageTitle'] = $onPageTitle;
        $bindings['SiteRole'] = $siteRole;

        return view('user.index', $bindings);
    }
}
