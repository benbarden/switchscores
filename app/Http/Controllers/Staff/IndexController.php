<?php

namespace App\Http\Controllers\Staff;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SiteRequestData;
use App\Traits\WosServices;

use App\SiteAlert;

class IndexController extends Controller
{
    use SiteRequestData;
    use WosServices;

    public function index()
    {
        $serviceUser = $this->getServiceUser();

        $serviceFeedItemGame = $this->getServiceFeedItemGame();
        $serviceFeedItemReview = $this->getServiceFeedItemReview();

        $serviceSiteAlert = $this->getServiceSiteAlert();
        $serviceEshopEurope = $this->getServiceEshopEuropeGame();

        $bindings = [];

        $pageTitle = 'Staff index';
        $bindings['TopTitle'] = $pageTitle;
        $bindings['PageTitle'] = $pageTitle;

        // Approvals
        $serviceMarioMakerLevels = $this->getServiceMarioMakerLevel();
        $bindings['MarioMakerLevelPendingCount'] = $serviceMarioMakerLevels->getPending()->count();

        // Information and site stats
        $bindings['RegisteredUserCount'] = $serviceUser->getCount();
        $bindings['EshopEuropeUnlinkedCount'] = $serviceEshopEurope->getAllWithoutLink(null, true);

        // Updates requiring approval
        $unprocessedFeedReviewItems = $serviceFeedItemReview->getUnprocessed();
        $pendingFeedGameItems = $serviceFeedItemGame->getPending();
        $bindings['UnprocessedFeedReviewItemsCount'] = count($unprocessedFeedReviewItems);
        $bindings['PendingFeedGameItemsCount'] = count($pendingFeedGameItems);

        // Action lists
        $bindings['SiteAlertErrorCount'] = $serviceSiteAlert->countByType(SiteAlert::TYPE_ERROR);
        $bindings['SiteAlertLatest'] = $serviceSiteAlert->getLatest(SiteAlert::TYPE_ERROR);

        return view('staff.index', $bindings);
    }
}
