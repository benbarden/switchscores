<?php

namespace App\Http\Controllers\Staff\Reviews;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SiteRequestData;
use App\Traits\WosServices;

use App\PartnerReview;
use App\QuickReview;

class DashboardController extends Controller
{
    use WosServices;
    use SiteRequestData;

    public function show()
    {
        $pageTitle = 'Reviews dashboard';

        $regionCode = $this->getRegionCode();

        $serviceFeedItemReview = $this->getServiceFeedItemReview();
        $servicePartnerReview = $this->getServicePartnerReview();
        $serviceQuickReview = $this->getServiceQuickReview();

        $serviceReviewLinks = $this->getServiceReviewLink();
        $serviceGameRankAllTime = $this->getServiceGameRankAllTime();
        $serviceTopRated = $this->getServiceTopRated();

        $bindings = [];

        // Action lists
        $unprocessedFeedReviewItems = $serviceFeedItemReview->getUnprocessed();
        $pendingPartnerReview = $servicePartnerReview->getByStatus(PartnerReview::STATUS_PENDING);
        $pendingQuickReview = $serviceQuickReview->getByStatus(QuickReview::STATUS_PENDING);
        $bindings['UnprocessedFeedReviewItemsCount'] = count($unprocessedFeedReviewItems);
        $bindings['PendingPartnerReviewCount'] = count($pendingPartnerReview);
        $bindings['PendingQuickReviewCount'] = count($pendingQuickReview);

        // Information
        $bindings['ReviewLinkCount'] = $serviceReviewLinks->countActive();
        $bindings['RankedGameCount'] = $serviceGameRankAllTime->countRanked();
        $bindings['UnrankedGameCount'] = $serviceTopRated->getUnrankedCount($regionCode);

        $bindings['TopTitle'] = $pageTitle.' - Admin';
        $bindings['PageTitle'] = $pageTitle;

        return view('staff.reviews.dashboard', $bindings);
    }
}
