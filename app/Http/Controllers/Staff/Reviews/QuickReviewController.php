<?php

namespace App\Http\Controllers\Staff\Reviews;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;

class QuickReviewController extends Controller
{
    use SwitchServices;

    public function showList()
    {
        $request = request();
        $filterStatus = $request->filterStatus;

        $serviceQuickReview = $this->getServiceQuickReview();

        $bindings = [];

        $bindings['TopTitle'] = 'Staff - Reviews - Quick reviews';
        $bindings['PageTitle'] = 'Quick reviews';

        $jsInitialSort = "[ 0, 'desc']";

        if (!isset($filterStatus)) {
            $bindings['FilterStatus'] = '';
            $reviewList = $serviceQuickReview->getAll();
        } else {
            $bindings['FilterStatus'] = $filterStatus;
            $reviewList = $serviceQuickReview->getByStatus($filterStatus);
        }

        $bindings['QuickReviewList'] = $reviewList;
        $bindings['QuickReviewStatusList'] = $serviceQuickReview->getStatusList();
        $bindings['jsInitialSort'] = $jsInitialSort;

        return view('staff.reviews.quick-reviews.list', $bindings);
    }

    public function edit($reviewId)
    {
        $request = request();

        $serviceQuickReview = $this->getServiceQuickReview();

        $reviewData = $serviceQuickReview->find($reviewId);
        if (!$reviewData) abort(404);

        $statusList = $serviceQuickReview->getStatusList();

        $bindings = [];

        if ($request->isMethod('post')) {

            $itemStatus = $request->item_status;

            $statusFound = false;
            foreach ($statusList as $statusListItem) {
                if ($statusListItem['id'] == $itemStatus) {
                    $statusFound = true;
                    break;
                }
            }
            if (!$statusFound) {
                throw new \Exception('Unknown status: '.$itemStatus);
            }

            $serviceQuickReview->editStatus($reviewData, $itemStatus);

            // All done; send us back
            return redirect(route('staff.reviews.quick-reviews.list'));

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['TopTitle'] = 'Staff - Quick reviews - Edit';
        $bindings['PageTitle'] = 'Edit quick review';
        $bindings['ReviewData'] = $reviewData;
        $bindings['ReviewId'] = $reviewId;

        $bindings['StatusList'] = $statusList;

        return view('staff.reviews.quick-reviews.edit', $bindings);
    }
}
