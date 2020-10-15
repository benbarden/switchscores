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

        // Unranked breakdown
        $bindings['UnrankedReviews2'] = $this->getServiceTopRated()->getUnrankedCountByReviewCount(2);
        $bindings['UnrankedReviews1'] = $this->getServiceTopRated()->getUnrankedCountByReviewCount(1);
        $bindings['UnrankedReviews0'] = $this->getServiceTopRated()->getUnrankedCountByReviewCount(0);
        $bindings['UnrankedYear2020'] = $this->getServiceTopRated()->getUnrankedCountByReleaseYear(2020);
        $bindings['UnrankedYear2019'] = $this->getServiceTopRated()->getUnrankedCountByReleaseYear(2019);
        $bindings['UnrankedYear2018'] = $this->getServiceTopRated()->getUnrankedCountByReleaseYear(2018);
        $bindings['UnrankedYear2017'] = $this->getServiceTopRated()->getUnrankedCountByReleaseYear(2017);

        $bindings['ProcessStatusStats'] = $serviceReviewFeedItem->getProcessStatusStats();

        $bindings['TopTitle'] = $pageTitle.' - Admin';
        $bindings['PageTitle'] = $pageTitle;

        return view('staff.reviews.dashboard', $bindings);
    }
}
