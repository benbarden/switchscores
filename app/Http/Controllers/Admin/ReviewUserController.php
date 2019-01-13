<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller as Controller;
use App\Services\ServiceContainer;

class ReviewUserController extends Controller
{
    public function showList()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $request = request();
        $filterStatus = $request->filterStatus;

        $serviceReviewUser = $serviceContainer->getReviewUserService();

        $bindings = [];

        $bindings['TopTitle'] = 'Admin - Reviews - User reviews';
        $bindings['PageTitle'] = 'User reviews';

        $jsInitialSort = "[ 3, 'desc']";

        if (!isset($filterStatus)) {
            $bindings['FilterStatus'] = '';
            $reviewUserList = $serviceReviewUser->getAll();
        } else {
            $bindings['FilterStatus'] = $filterStatus;
            $reviewUserList = $serviceReviewUser->getByStatus($filterStatus);
        }

        $bindings['ReviewUserList'] = $reviewUserList;
        $bindings['ReviewUserStatusList'] = $serviceReviewUser->getStatusList();
        $bindings['jsInitialSort'] = $jsInitialSort;

        return view('admin.reviews.user.list', $bindings);
    }

    public function edit($reviewId)
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $request = request();

        $regionCode = \Request::get('regionCode');

        $serviceReviewUser = $serviceContainer->getReviewUserService();

        $reviewUserData = $serviceReviewUser->find($reviewId);
        if (!$reviewUserData) abort(404);

        $reviewUserStatusList = $serviceReviewUser->getStatusList();

        $bindings = [];

        if ($request->isMethod('post')) {

            $itemStatus = $request->item_status;

            $statusFound = false;
            foreach ($reviewUserStatusList as $statusListItem) {
                if ($statusListItem['id'] == $itemStatus) {
                    $statusFound = true;
                    break;
                }
            }
            if (!$statusFound) {
                throw new \Exception('Unknown status: '.$itemStatus);
            }

            $serviceReviewUser->editStatus($reviewUserData, $itemStatus);

            // All done; send us back
            return redirect(route('admin.reviews.user.list'));

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['TopTitle'] = 'Admin - User reviews - Edit';
        $bindings['PageTitle'] = 'Edit user review';
        $bindings['ReviewUserData'] = $reviewUserData;
        $bindings['ReviewId'] = $reviewId;

        $bindings['ReviewUserStatusList'] = $reviewUserStatusList;

        return view('admin.reviews.user.edit', $bindings);
    }
}
