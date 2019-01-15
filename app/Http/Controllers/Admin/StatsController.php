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
        $bindings['TopTitle'] = 'Admin - Stats - Review sites';

        return view('admin.stats.review.site', $bindings);
    }

    public function oldDeveloperMultiple()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $serviceGame = $serviceContainer->getGameService();

        $bindings = [];

        $bindings['ItemList'] = $serviceGame->getOldDevelopersMultiple();

        $bindings['PageTitle'] = 'Old developers - multiple records';
        $bindings['TopTitle'] = 'Admin - Stats - Old developers - multiple records';

        return view('admin.stats.games.old-developer-multiple', $bindings);
    }

    public function oldPublisherMultiple()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $serviceGame = $serviceContainer->getGameService();

        $bindings = [];

        $bindings['ItemList'] = $serviceGame->getOldPublishersMultiple();

        $bindings['PageTitle'] = 'Old publishers - multiple records';
        $bindings['TopTitle'] = 'Admin - Stats - Old publishers - multiple records';

        return view('admin.stats.games.old-publisher-multiple', $bindings);
    }

    public function oldDeveloperByCount()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $serviceGame = $serviceContainer->getGameService();

        $bindings = [];

        $bindings['ItemList'] = $serviceGame->getOldDevelopersByCount();

        $bindings['PageTitle'] = 'Old developers - by count';
        $bindings['TopTitle'] = 'Admin - Stats - Old developers - by count';

        return view('admin.stats.games.old-developer-by-count', $bindings);
    }

    public function oldPublisherByCount()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $serviceGame = $serviceContainer->getGameService();

        $bindings = [];

        $bindings['ItemList'] = $serviceGame->getOldPublishersByCount();

        $bindings['PageTitle'] = 'Old publishers - by count';
        $bindings['TopTitle'] = 'Admin - Stats - Old publishers - by count';

        return view('admin.stats.games.old-publisher-by-count', $bindings);
    }

    public function oldDeveloperGameList($developer)
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $regionCode = \Request::get('regionCode');

        $serviceGame = $serviceContainer->getGameService();

        $bindings = [];

        $bindings['ItemList'] = $serviceGame->getByDeveloper($regionCode, $developer);

        $bindings['PageTitle'] = 'Old developers - Game list';
        $bindings['TopTitle'] = 'Admin - Stats - Old developers - Game list';

        return view('admin.stats.games.old-developer-game-list', $bindings);
    }

    public function oldPublisherGameList($publisher)
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $regionCode = \Request::get('regionCode');

        $serviceGame = $serviceContainer->getGameService();

        $bindings = [];

        $bindings['ItemList'] = $serviceGame->getByPublisher($regionCode, $publisher);

        $bindings['PageTitle'] = 'Old publishers - by count';
        $bindings['TopTitle'] = 'Admin - Stats - Old publishers - by count';

        return view('admin.stats.games.old-publisher-game-list', $bindings);
    }
}
