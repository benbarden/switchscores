<?php

namespace App\Http\Controllers\Staff\Reviews;

use Illuminate\Routing\Controller as Controller;

use App\QuickReview;

use App\Traits\SwitchServices;

class DashboardController extends Controller
{
    use SwitchServices;

    public function show()
    {
        $pageTitle = 'Reviews dashboard';

        $serviceReviewFeedItem = $this->getServiceReviewFeedItem();
        $serviceQuickReview = $this->getServiceQuickReview();

        $serviceReviewLinks = $this->getServiceReviewLink();
        $serviceGame = $this->getServiceGame();
        $serviceTopRated = $this->getServiceTopRated();

        $bindings = [];

        // Action lists
        $unprocessedFeedReviewItems = $serviceReviewFeedItem->getUnprocessed();
        $pendingQuickReview = $serviceQuickReview->getByStatus(QuickReview::STATUS_PENDING);
        $bindings['UnprocessedFeedReviewItemsCount'] = count($unprocessedFeedReviewItems);
        $bindings['PendingQuickReviewCount'] = count($pendingQuickReview);

        // Feed imports
        $bindings['ReviewFeedImportList'] = $this->getServiceReviewFeedImport()->getAll(10);

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
