<?php

namespace App\Http\Controllers\Staff;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;

use App\QuickReview;
use App\SiteAlert;

class IndexController extends Controller
{
    use SwitchServices;

    public function index()
    {
        $serviceUser = $this->getServiceUser();

        $serviceFeedItemGame = $this->getServiceFeedItemGame();
        $serviceReviewFeedItem = $this->getServiceReviewFeedItem();
        $serviceQuickReview = $this->getServiceQuickReview();

        $serviceSiteAlert = $this->getServiceSiteAlert();
        $serviceEshopEurope = $this->getServiceEshopEuropeGame();

        $bindings = [];

        $pageTitle = 'Staff index';
        $bindings['TopTitle'] = $pageTitle;
        $bindings['PageTitle'] = $pageTitle;

        // Information and site stats
        $bindings['RegisteredUserCount'] = $serviceUser->getCount();
        $ignoreFsIdList = $this->getServiceEshopEuropeIgnore()->getIgnoredFsIdList();
        $bindings['EshopEuropeUnlinkedCount'] = $serviceEshopEurope->getAllWithoutLink($ignoreFsIdList, null, true);

        // Updates requiring approval
        $unprocessedFeedReviewItems = $serviceReviewFeedItem->getUnprocessed();
        $pendingFeedGameItems = $serviceFeedItemGame->getPending();
        $pendingQuickReview = $serviceQuickReview->getByStatus(QuickReview::STATUS_PENDING);
        $bindings['UnprocessedFeedReviewItemsCount'] = count($unprocessedFeedReviewItems);
        $bindings['PendingFeedGameItemsCount'] = count($pendingFeedGameItems);
        $bindings['PendingQuickReviewCount'] = count($pendingQuickReview);

        // Action lists
        $bindings['SiteAlertErrorCount'] = $serviceSiteAlert->countByType(SiteAlert::TYPE_ERROR);
        $bindings['SiteAlertLatest'] = $serviceSiteAlert->getLatest(SiteAlert::TYPE_ERROR);

        return view('staff.index', $bindings);
    }
}
