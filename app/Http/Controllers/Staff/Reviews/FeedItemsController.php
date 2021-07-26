<?php

namespace App\Http\Controllers\Staff\Reviews;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Traits\SwitchServices;
use App\Traits\StaffView;

class FeedItemsController extends Controller
{
    use SwitchServices;
    use StaffView;

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRules = [
        'site_id' => 'required',
    ];

    public function showList($report = null)
    {
        $bindings = $this->getBindingsReviewsSubpage('Feed items');

        $serviceReviewFeedItem = $this->getServiceReviewFeedItem();

        $bindings['jsInitialSort'] = '[0, "asc"]';

        if ($report == null) {
            $bindings['ActiveNav'] = '';
            $feedItems = $serviceReviewFeedItem->getUnprocessed();
        } else {
            $bindings['ActiveNav'] = $report;
            switch ($report) {
                case 'processed':
                    $itemLimit = 250;
                    $bindings['TableLimit'] = $itemLimit;
                    $feedItems = $serviceReviewFeedItem->getProcessed($itemLimit);
                    break;
                default:
                    abort(404);
                    break;
            }
        }

        $bindings['FeedItems'] = $feedItems;

        return view('staff.reviews.feed-items.list', $bindings);
    }

    public function byProcessStatus($status)
    {
        $bindings = $this->getBindingsReviewsSubpage('Feed items');

        $bindings['FeedItems'] = $this->getServiceReviewFeedItem()->getByProcessStatus($status);

        $bindings['HideFilters'] = 'Y';

        return view('staff.reviews.feed-items.list', $bindings);
    }

    public function edit($itemId)
    {
        $bindings = $this->getBindingsReviewsFeedItemsSubpage('Edit feed item');

        $feedItemData = $this->getServiceReviewFeedItem()->find($itemId);
        if (!$feedItemData) abort(404);

        $request = request();

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';

            $this->validate($request, $this->validationRules);

            $this->getServiceReviewFeedItem()->edit(
                $feedItemData, $request->site_id, $request->game_id, $request->item_rating,
                $request->process_status
            );

            // All done; send us back
            return redirect(route('staff.reviews.feed-items.list'));

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['FeedItemData'] = $feedItemData;
        $bindings['ItemId'] = $itemId;

        $bindings['GamesList'] = $this->getServiceGame()->getAll();

        $bindings['ReviewSites'] = $this->getServicePartner()->getAllReviewSites();

        $bindings['ProcessStatusSuccess'] = $this->getServiceReviewFeedItem()->getProcessOptionsSuccess();
        $bindings['ProcessStatusFailure'] = $this->getServiceReviewFeedItem()->getProcessOptionsFailure();

        return view('staff.reviews.feed-items.edit', $bindings);
    }
}
