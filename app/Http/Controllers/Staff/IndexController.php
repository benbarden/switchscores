<?php

namespace App\Http\Controllers\Staff;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;
use App\Traits\StaffView;

use App\Services\DataQuality\QualityStats;
use App\QuickReview;

class IndexController extends Controller
{
    use SwitchServices;
    use StaffView;

    public function index()
    {
        $bindings = $this->getBindingsDashboard('Staff index');

        $serviceQualityStats = new QualityStats();
        $serviceUser = $this->getServiceUser();

        $serviceReviewFeedItem = $this->getServiceReviewFeedItem();
        $serviceQuickReview = $this->getServiceQuickReview();

        // Updates requiring approval
        $unprocessedFeedReviewItems = $serviceReviewFeedItem->getUnprocessed();
        $pendingQuickReview = $serviceQuickReview->getByStatus(QuickReview::STATUS_PENDING);
        $bindings['UnprocessedFeedReviewItemsCount'] = count($unprocessedFeedReviewItems);
        $bindings['PendingQuickReviewCount'] = count($pendingQuickReview);

        // Game category suggestions
        $pendingCategoryEdits = $this->getServiceDbEditGame()->getPendingCategoryEdits();
        $bindings['PendingGameCategorySuggestionCount'] = count($pendingCategoryEdits);

        // Nintendo.co.uk: Unlinked items
        $ignoreIdList = $this->getServiceDataSourceIgnore()->getNintendoCoUkLinkIdList();
        $unlinkedItemList = $this->getServiceDataSourceParsed()->getAllNintendoCoUkWithNoGameId($ignoreIdList);
        $bindings['NintendoCoUkUnlinkedCount'] = $unlinkedItemList->count();

        // Games to release
        $actionListGamesForReleaseCount = $this->getServiceGame()->getActionListGamesForRelease();
        $bindings['GamesForReleaseCount'] = count($actionListGamesForReleaseCount);

        // Data integrity
        $bindings['DuplicateReviewsCount'] = count($serviceQualityStats->getDuplicateReviews());

        // Feed imports
        $bindings['ReviewFeedImportList'] = $this->getServiceReviewFeedImport()->getLive(5);

        // Information and site stats
        $bindings['RegisteredUserCount'] = $serviceUser->getCount();

        return view('staff.index', $bindings);
    }
}
