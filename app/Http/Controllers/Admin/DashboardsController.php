<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller as Controller;

use App\Traits\WosServices;

use App\Services\ServiceContainer;

use App\SiteAlert;
use App\ReviewUser;
use App\PartnerReview;

use App\Services\AdminDashboards\CategorisationService;

class DashboardsController extends Controller
{
    use WosServices;

    public function feedItemsLanding()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $bindings = [];

        $bindings['TopTitle'] = 'Feed items';
        $bindings['PageTitle'] = 'Feed items';

        return view('admin.feed-items.landing', $bindings);
    }

    public function index()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $regionCode = \Request::get('regionCode');

        $serviceUser = $serviceContainer->getUserService();

        $feedItemGameService = $serviceContainer->getFeedItemGameService();
        $serviceFeedItemReview = $serviceContainer->getFeedItemReviewService();

        $serviceSiteAlert = $serviceContainer->getSiteAlertService();
        $serviceEshopEurope = $serviceContainer->getEshopEuropeGameService();

        $bindings = [];

        $pageTitle = 'Admin dashboard';
        $bindings['TopTitle'] = $pageTitle;
        $bindings['PageTitle'] = $pageTitle;

        // Approvals
        $serviceMarioMakerLevels = $serviceContainer->getMarioMakerLevelService();
        $bindings['MarioMakerLevelPendingCount'] = $serviceMarioMakerLevels->getPending()->count();

        // Information and site stats
        $bindings['RegisteredUserCount'] = $serviceUser->getCount();
        $bindings['EshopEuropeUnlinkedCount'] = $serviceEshopEurope->getAllWithoutLink(null, true);

        // Updates requiring approval
        $unprocessedFeedReviewItems = $serviceFeedItemReview->getUnprocessed();
        $pendingFeedGameItems = $feedItemGameService->getPending();
        $bindings['UnprocessedFeedReviewItemsCount'] = count($unprocessedFeedReviewItems);
        $bindings['PendingFeedGameItemsCount'] = count($pendingFeedGameItems);

        // Action lists
        $bindings['SiteAlertErrorCount'] = $serviceSiteAlert->countByType(SiteAlert::TYPE_ERROR);
        $bindings['SiteAlertLatest'] = $serviceSiteAlert->getLatest(SiteAlert::TYPE_ERROR);

        return view('admin.dashboards.index', $bindings);
    }
}
