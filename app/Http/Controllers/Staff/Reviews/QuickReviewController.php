<?php

namespace App\Http\Controllers\Staff\Reviews;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;
use App\Traits\StaffView;

use App\QuickReview;
use App\Factories\UserFactory;
use App\Factories\UserPointTransactionDirectorFactory;

class QuickReviewController extends Controller
{
    use SwitchServices;
    use StaffView;

    public function showList()
    {
        $bindings = $this->getBindingsReviewsSubpage('Quick reviews');

        $request = request();
        $filterStatus = $request->filterStatus;

        if (!isset($filterStatus)) {
            $bindings['FilterStatus'] = '';
            $reviewList = $this->getServiceQuickReview()->getAll();
        } else {
            $bindings['FilterStatus'] = $filterStatus;
            $reviewList = $this->getServiceQuickReview()->getByStatus($filterStatus);
        }

        $bindings['QuickReviewList'] = $reviewList;
        $bindings['QuickReviewStatusList'] = $this->getServiceQuickReview()->getStatusList();

        return view('staff.reviews.quick-reviews.list', $bindings);
    }

    public function edit($reviewId)
    {
        $bindings = $this->getBindingsReviewsQuickReviewsSubpage('Edit quick review');

        $request = request();

        $reviewData = $this->getServiceQuickReview()->find($reviewId);
        if (!$reviewData) abort(404);

        $statusList = $this->getServiceQuickReview()->getStatusList();

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

            $this->getServiceQuickReview()->editStatus($reviewData, $itemStatus);

            if ($itemStatus == QuickReview::STATUS_ACTIVE) {

                $userId = $reviewData->user_id;
                $gameId = $reviewData->game_id;

                // Update game review stats
                $game = $this->getServiceGame()->find($gameId);
                $reviewLinks = $this->getServiceReviewLink()->getByGame($gameId);
                $quickReviews = $this->getServiceQuickReview()->getActiveByGame($gameId);
                $this->getServiceReviewStats()->updateGameReviewStats($game, $reviewLinks, $quickReviews);

                // Credit points
                $user = $this->getServiceUser()->find($userId);
                UserFactory::addPointsForQuickReview($user);

                // Store the transaction
                UserPointTransactionDirectorFactory::addForQuickReview($userId, $gameId);

            }

            // All done; send us back
            return redirect(route('staff.reviews.quick-reviews.list'));

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['ReviewData'] = $reviewData;
        $bindings['ReviewId'] = $reviewId;

        $bindings['StatusList'] = $statusList;

        return view('staff.reviews.quick-reviews.edit', $bindings);
    }

    public function delete($reviewId)
    {
        $bindings = $this->getBindingsReviewsQuickReviewsSubpage('Delete quick review');

        $reviewData = $this->getServiceQuickReview()->find($reviewId);
        if (!$reviewData) abort(404);

        $request = request();

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'delete-post';

            $gameId = $reviewData->game_id;

            $this->getServiceQuickReview()->delete($reviewId);

            $game = $this->getServiceGame()->find($gameId);
            if ($game) {
                // Update game review stats
                $reviewLinks = $this->getServiceReviewLink()->getByGame($gameId);
                $quickReviews = $this->getServiceQuickReview()->getActiveByGame($gameId);
                $this->getServiceReviewStats()->updateGameReviewStats($game, $reviewLinks, $quickReviews);
            }

            // Done

            return redirect(route('staff.reviews.quick-reviews.list'));

        } else {

            $bindings['FormMode'] = 'delete';

        }

        $bindings['QuickReview'] = $reviewData;
        $bindings['ReviewId'] = $reviewId;

        return view('staff.reviews.quick-reviews.delete', $bindings);
    }
}
