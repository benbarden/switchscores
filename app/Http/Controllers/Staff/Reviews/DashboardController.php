<?php

namespace App\Http\Controllers\Staff\Reviews;

use Illuminate\Routing\Controller as Controller;

use App\PartnerReview;
use App\QuickReview;

use App\Traits\SwitchServices;

class DashboardController extends Controller
{
    use SwitchServices;

    public function show()
    {
        $pageTitle = 'Reviews dashboard';

        $serviceReviewFeedItem = $this->getServiceReviewFeedItem();
        $servicePartnerReview = $this->getServicePartnerReview();
        $serviceQuickReview = $this->getServiceQuickReview();

        $serviceReviewLinks = $this->getServiceReviewLink();
        $serviceGame = $this->getServiceGame();
        $serviceTopRated = $this->getServiceTopRated();

        $bindings = [];

        // Action lists
        $unprocessedFeedReviewItems = $serviceReviewFeedItem->getUnprocessed();
        $pendingPartnerReview = $servicePartnerReview->getByStatus(PartnerReview::STATUS_PENDING);
        $pendingQuickReview = $serviceQuickReview->getByStatus(QuickReview::STATUS_PENDING);
        $bindings['UnprocessedFeedReviewItemsCount'] = count($unprocessedFeedReviewItems);
        $bindings['PendingPartnerReviewCount'] = count($pendingPartnerReview);
        $bindings['PendingQuickReviewCount'] = count($pendingQuickReview);

        // Stats
        $bindings['ReviewLinkCount'] = $serviceReviewLinks->countActive();
        $bindings['RankedGameCount'] = $serviceGame->countRanked();
        $bindings['UnrankedGameCount'] = $serviceTopRated->getUnrankedCount();

        $bindings['ProcessStatusStats'] = $serviceReviewFeedItem->getProcessStatusStats();

        $bindings['TopTitle'] = $pageTitle.' - Admin';
        $bindings['PageTitle'] = $pageTitle;

        return view('staff.reviews.dashboard', $bindings);
    }
}
