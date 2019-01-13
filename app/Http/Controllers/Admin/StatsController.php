<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller as Controller;
use App\Services\ServiceContainer;

class StatsController extends Controller
{
    public function landing()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $bindings = [];

        $bindings['PageTitle'] = 'Stats';
        $bindings['TopTitle'] = 'Admin - Stats';

        return view('admin.stats.landing', $bindings);
    }

    public function reviewSite()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $regionCode = \Request::get('regionCode');

        $bindings = [];

        $serviceReviewLinks = $serviceContainer->getReviewLinkService();
        $serviceReviewSite = $serviceContainer->getReviewSiteService();
        $serviceGameReleaseDate = $serviceContainer->getGameReleaseDateService();
        $serviceTopRated = $serviceContainer->getTopRatedService();
        $serviceGame = $serviceContainer->getGameService();
        $serviceReviewStats = $serviceContainer->getReviewStatsService();

        $bindings['RankedGameCount'] = $serviceTopRated->getCount($regionCode);
        $bindings['UnrankedGameCount'] = $serviceTopRated->getUnrankedCount($regionCode);

        $releasedGameCount = $serviceGameReleaseDate->countReleased($regionCode);
        $reviewLinkCount = $serviceReviewLinks->countActive();

        $bindings['ReleasedGameCount'] = $releasedGameCount;
        $bindings['ReviewLinkCount'] = $reviewLinkCount;

        $reviewSitesActive = $serviceReviewSite->getActive();
        $reviewSitesRender = [];

        foreach ($reviewSitesActive as $reviewSite) {

            $id = $reviewSite->id;
            $name = $reviewSite->name;
            $linkTitle = $reviewSite->link_title;

            $reviewCount = $serviceReviewLinks->countBySite($id);
            $reviewLinkContribTotal = $serviceReviewStats->calculateContributionPercentage($reviewCount, $reviewLinkCount);
            $reviewGameCompletionTotal = $serviceReviewStats->calculateContributionPercentage($reviewCount, $releasedGameCount);

            $reviewSitesRender[] = [
                'id' => $id,
                'name' => $name,
                'link_title' => $linkTitle,
                'review_count' => $reviewCount,
                'review_link_contrib_total' => $reviewLinkContribTotal,
                'review_game_completion_total' => $reviewGameCompletionTotal,
            ];

        }

        $bindings['ReviewSitesArray'] = $reviewSitesRender;

        $bindings['PageTitle'] = 'Review site stats';
        $bindings['TopTitle'] = 'Admin - Review site stats';

        return view('admin.stats.review.site', $bindings);
    }
}
