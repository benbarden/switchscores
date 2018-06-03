<?php

namespace App\Http\Controllers\Admin;

use App\Services\FeedItemReviewService;
use Illuminate\Http\Request;

class FeedItemController extends \App\Http\Controllers\BaseController
{
    /**
     * @var array
     */
    private $validationRules = [
        'site_id' => 'required',
    ];

    public function showList($report = null)
    {
        $bindings = array();

        $bindings['TopTitle'] = 'Admin - Feed items';
        $bindings['PanelTitle'] = 'Feed items';

        $feedItemReviewService = resolve('Services\FeedItemReviewService');
        /* @var FeedItemReviewService $feedItemReviewService */

        if ($report == null) {
            $bindings['ActiveNav'] = '';
            $feedItems = $feedItemReviewService->getUnprocessed();
            $jsInitialSort = "[ 2, 'asc']";
        } else {
            $bindings['ActiveNav'] = $report;
            switch ($report) {
                case 'processed':
                    $feedItems = $feedItemReviewService->getProcessed();
                    $jsInitialSort = "[ 2, 'desc']";
                    break;
                default:
                    abort(404);
                    break;
            }
        }

        $bindings['FeedItems'] = $feedItems;
        $bindings['jsInitialSort'] = $jsInitialSort;

        return view('admin.feed-items.list', $bindings);
    }

    public function edit($itemId)
    {
        $feedItemReviewService = resolve('Services\FeedItemReviewService');
        /* @var FeedItemReviewService $feedItemReviewService */

        $feedItemData = $feedItemReviewService->find($itemId);
        if (!$feedItemData) abort(404);

        $gameService = $this->serviceContainer->getGameService();
        
        $reviewSiteService = resolve('Services\ReviewSiteService');

        $request = request();
        $bindings = array();

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';

            $this->validate($request, $this->validationRules);

            $feedItemReviewService->edit(
                $feedItemData, $request->site_id, $request->game_id, $request->item_rating,
                $request->processed, $request->process_status
            );

            // All done; send us back
            return redirect(route('admin.feed-items.list'));

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['TopTitle'] = 'Admin - Feed items - Edit';
        $bindings['PanelTitle'] = 'Edit feed item';
        $bindings['FeedItemData'] = $feedItemData;
        $bindings['ItemId'] = $itemId;

        $bindings['GamesList'] = $gameService->getAll($this->region);

        $bindings['ReviewSites'] = $reviewSiteService->getAll();

        return view('admin.feed-items.edit', $bindings);
    }
}
