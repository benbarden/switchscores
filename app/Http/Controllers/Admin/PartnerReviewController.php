<?php

namespace App\Http\Controllers\Admin;

use App\PartnerReview;
use App\ReviewLink;
use Illuminate\Routing\Controller as Controller;
use App\Services\ServiceContainer;

class PartnerReviewController extends Controller
{
    public function showList()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $request = request();
        $filterStatus = $request->filterStatus;

        $servicePartnerReview = $serviceContainer->getPartnerReviewService();

        $bindings = [];

        $bindings['TopTitle'] = 'Admin - Reviews - Partner reviews';
        $bindings['PanelTitle'] = 'Partner reviews';

        $jsInitialSort = "[ 0, 'desc']";

        if (!isset($filterStatus)) {
            $bindings['FilterStatus'] = '';
            $reviewList = $servicePartnerReview->getAll();
        } else {
            $bindings['FilterStatus'] = $filterStatus;
            $reviewList = $servicePartnerReview->getByStatus($filterStatus);
        }

        $bindings['ReviewList'] = $reviewList;
        $bindings['ReviewStatusList'] = $servicePartnerReview->getStatusList();
        $bindings['jsInitialSort'] = $jsInitialSort;

        return view('admin.reviews.partner.list', $bindings);
    }

    public function edit($reviewId)
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $request = request();

        $servicePartnerReview = $serviceContainer->getPartnerReviewService();
        $serviceReviewUser = $serviceContainer->getReviewUserService();

        $reviewData = $servicePartnerReview->find($reviewId);
        if (!$reviewData) abort(404);

        // Don't allow a linked review to be re-edited
        if ($reviewData->review_link_id) {
            return redirect(route('admin.reviews.partner.list'));
        }

        $reviewStatusList = $servicePartnerReview->getStatusList();

        $bindings = [];

        if ($request->isMethod('post')) {

            $itemStatus = $request->item_status;

            $statusFound = false;
            foreach ($reviewStatusList as $statusListItem) {
                if ($statusListItem['id'] == $itemStatus) {
                    $statusFound = true;
                    break;
                }
            }
            if (!$statusFound) {
                throw new \Exception('Unknown status: '.$itemStatus);
            }

            if ($itemStatus == PartnerReview::STATUS_ACTIVE) {

                $reviewSiteService = $serviceContainer->getReviewSiteService();
                $reviewLinkService = $serviceContainer->getReviewLinkService();
                $reviewStatsService = $serviceContainer->getReviewStatsService();
                $gameService = $serviceContainer->getGameService();

                // Create the review
                $gameId = $reviewData->game_id;
                $siteId = $reviewData->site_id;
                $reviewUserId = $reviewData->user_id;
                $itemRating = $reviewData->item_rating;
                $itemUrl = $reviewData->item_url;
                $itemDateShort = date('Y-m-d', strtotime($reviewData->item_date));

                $reviewSite = $reviewSiteService->find($siteId);
                $ratingNormalised = $reviewLinkService->getNormalisedRating($itemRating, $reviewSite);

                $reviewLink = $reviewLinkService->create(
                    $gameId, $siteId, $itemUrl, $itemRating, $ratingNormalised, $itemDateShort,
                    ReviewLink::TYPE_PARTNER, $reviewUserId
                );

                // Update game review stats
                $game = $gameService->find($gameId);
                $reviewStatsService->updateGameReviewStats($game);

                // Update review link
                $reviewData->review_link_id = $reviewLink->id;

            }

            $servicePartnerReview->editStatus($reviewData, $itemStatus);

            // All done; send us back
            return redirect(route('admin.reviews.partner.list'));

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['TopTitle'] = 'Admin - Partner reviews - Edit';
        $bindings['PanelTitle'] = 'Edit partner review';
        $bindings['ReviewData'] = $reviewData;
        $bindings['ReviewId'] = $reviewId;

        $bindings['ReviewStatusList'] = $reviewStatusList;

        return view('admin.reviews.partner.edit', $bindings);
    }
}
