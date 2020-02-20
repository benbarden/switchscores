<?php

namespace App\Http\Controllers\Staff\Reviews;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Traits\SwitchServices;

class FeedItemsController extends Controller
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

        $bindings['TopTitle'] = 'Reviews - Feed items';
        $bindings['PageTitle'] = 'Feed items';

        $serviceReviewFeedItem = $this->getServiceReviewFeedItem();

        if ($report == null) {
            $bindings['ActiveNav'] = '';
            $feedItems = $serviceReviewFeedItem->getUnprocessed();
            $jsInitialSort = "[ 2, 'desc']";
        } else {
            $bindings['ActiveNav'] = $report;
            switch ($report) {
                case 'processed':
                    $itemLimit = 250;
                    $bindings['TableLimit'] = $itemLimit;
                    $feedItems = $serviceReviewFeedItem->getProcessed($itemLimit);
                    $jsInitialSort = "[ 2, 'desc']";
                    break;
                default:
                    abort(404);
                    break;
            }
        }

        $bindings['FeedItems'] = $feedItems;
        $bindings['jsInitialSort'] = $jsInitialSort;

        return view('staff.reviews.feed-items.list', $bindings);
    }

    public function edit($itemId)
    {
        $serviceReviewFeedItem = $this->getServiceReviewFeedItem();
        $serviceGame = $this->getServiceGame();
        $servicePartner = $this->getServicePartner();

        $feedItemData = $serviceReviewFeedItem->find($itemId);
        if (!$feedItemData) abort(404);

        $bindings = [];

        $request = request();

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';

            $this->validate($request, $this->validationRules);

            $serviceReviewFeedItem->edit(
                $feedItemData, $request->site_id, $request->game_id, $request->item_rating,
                $request->processed, $request->process_status
            );

            // All done; send us back
            return redirect(route('staff.reviews.feed-items.list'));

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['TopTitle'] = 'Reviews - Feed items - Edit';
        $bindings['PageTitle'] = 'Edit feed item';
        $bindings['FeedItemData'] = $feedItemData;
        $bindings['ItemId'] = $itemId;

        $bindings['GamesList'] = $serviceGame->getAll();

        $bindings['ReviewSites'] = $servicePartner->getAllReviewSites();

        return view('staff.reviews.feed-items.edit', $bindings);
    }
}
