<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Traits\SwitchServices;

class FeedItemReviewController extends Controller
{
    use SwitchServices;

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRules = [
        'site_id' => 'required',
    ];

    public function showList($report = null)
    {
        $bindings = [];

        $bindings['TopTitle'] = 'Admin - Feed items';
        $bindings['PageTitle'] = 'Feed items';

        $serviceFeedItemReview = $this->getServiceFeedItemReview();

        if ($report == null) {
            $bindings['ActiveNav'] = '';
            $feedItems = $serviceFeedItemReview->getUnprocessed();
            $jsInitialSort = "[ 2, 'asc']";
        } else {
            $bindings['ActiveNav'] = $report;
            switch ($report) {
                case 'processed':
                    $itemLimit = 250;
                    $bindings['TableLimit'] = $itemLimit;
                    $feedItems = $serviceFeedItemReview->getProcessed($itemLimit);
                    $jsInitialSort = "[ 2, 'desc']";
                    break;
                default:
                    abort(404);
                    break;
            }
        }

        $bindings['FeedItems'] = $feedItems;
        $bindings['jsInitialSort'] = $jsInitialSort;

        return view('admin.feed-items.reviews.list', $bindings);
    }

    public function edit($itemId)
    {
        $serviceFeedItemReview = $this->getServiceFeedItemReview();
        $serviceGame = $this->getServiceGame();
        $servicePartner = $this->getServicePartner();

        $feedItemData = $serviceFeedItemReview->find($itemId);
        if (!$feedItemData) abort(404);

        $bindings = [];

        $request = request();

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';

            $this->validate($request, $this->validationRules);

            $serviceFeedItemReview->edit(
                $feedItemData, $request->site_id, $request->game_id, $request->item_rating,
                $request->processed, $request->process_status
            );

            // All done; send us back
            return redirect(route('admin.feed-items.reviews.list'));

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['TopTitle'] = 'Admin - Feed items - Edit';
        $bindings['PageTitle'] = 'Edit feed item';
        $bindings['FeedItemData'] = $feedItemData;
        $bindings['ItemId'] = $itemId;

        $bindings['GamesList'] = $serviceGame->getAll();

        $bindings['ReviewSites'] = $servicePartner->getAllReviewSites();

        return view('admin.feed-items.reviews.edit', $bindings);
    }
}
