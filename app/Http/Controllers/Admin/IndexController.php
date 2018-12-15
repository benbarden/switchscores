<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller as Controller;
use App\Services\ServiceContainer;

use App\ReviewUser;
use App\PartnerReview;

class IndexController extends Controller
{
    public function feedItemsLanding()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $bindings = [];

        $bindings['TopTitle'] = 'Feed items';
        $bindings['PanelTitle'] = 'Feed items';

        return view('admin.feed-items.landing', $bindings);
    }

    public function show()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $regionCode = \Request::get('regionCode');

        $reviewUserService = $serviceContainer->getReviewUserService();
        $partnerReviewService = $serviceContainer->getPartnerReviewService();
        $feedItemGameService = $serviceContainer->getFeedItemGameService();
        $feedItemReviewService = $serviceContainer->getFeedItemReviewService();
        $userService = $serviceContainer->getUserService();
        $gameTagService = $serviceContainer->getGameTagService();
        $gameGenreService = $serviceContainer->getGameGenreService();
        $gameService = $serviceContainer->getGameService();

        $bindings = [];

        $bindings['TopTitle'] = 'Admin index';
        $bindings['PanelTitle'] = 'Admin index';


        // Action lists
        $actionListGamesForReleaseCountEu = $gameService->getActionListGamesForRelease('eu');
        $actionListGamesForReleaseCountUs = $gameService->getActionListGamesForRelease('us');
        $actionListGamesForReleaseCountJp = $gameService->getActionListGamesForRelease('jp');
        $pendingFeedGameItems = $feedItemGameService->getPending();
        $unprocessedFeedReviewItems = $feedItemReviewService->getUnprocessed();
        $pendingReviewUser = $reviewUserService->getByStatus(ReviewUser::STATUS_PENDING);
        $pendingPartnerReview = $partnerReviewService->getByStatus(PartnerReview::STATUS_PENDING);

        $bindings['ActionListGamesForReleaseCountEu'] = count($actionListGamesForReleaseCountEu);
        $bindings['ActionListGamesForReleaseCountUs'] = count($actionListGamesForReleaseCountUs);
        $bindings['ActionListGamesForReleaseCountJp'] = count($actionListGamesForReleaseCountJp);
        $bindings['PendingFeedGameItemsCount'] = count($pendingFeedGameItems);
        $bindings['UnprocessedFeedReviewItemsCount'] = count($unprocessedFeedReviewItems);
        $bindings['PendingReviewUserCount'] = count($pendingReviewUser);
        $bindings['PendingPartnerReviewCount'] = count($pendingPartnerReview);

        // Action lists
        $actionListNintendoUrlNoPackshotCount = $gameService->getActionListNintendoUrlNoPackshots($regionCode);
        $actionListRecentNoNintendoUrlCount = $gameService->getActionListRecentNoNintendoUrl($regionCode);
        $actionListUpcomingNoNintendoUrlCount = $gameService->getActionListUpcomingNoNintendoUrl($regionCode);

        $bindings['ActionListNintendoUrlNoPackshotsCount'] = count($actionListNintendoUrlNoPackshotCount);
        $bindings['ActionListRecentNoNintendoUrlCount'] = count($actionListRecentNoNintendoUrlCount);
        $bindings['ActionListUpcomingNoNintendoUrlCount'] = count($actionListUpcomingNoNintendoUrlCount);


        // Missing data
        $missingDevOrPub = $gameService->getWithoutDevOrPub();
        $missingTags = $gameTagService->getGamesWithoutTags($regionCode);
        $missingGenres = $gameGenreService->getGamesWithoutGenres($regionCode);
        $missingVendorPageUrl = $gameService->getByNullField('vendor_page_url', $regionCode);
        $missingVideoUrl = $gameService->getByNullField('video_url', $regionCode);
        $missingTwitterId = $gameService->getByNullField('twitter_id', $regionCode);
        $missingAmazonUkLink = $gameService->getWithoutAmazonUkLink();

        $bindings['MissingDevOrPubCount'] = count($missingDevOrPub);
        $bindings['MissingTagsCount'] = count($missingTags);
        $bindings['MissingGenresCount'] = count($missingGenres);
        $bindings['MissingVendorPageUrlCount'] = count($missingVendorPageUrl);
        $bindings['MissingVideoUrlCount'] = count($missingVideoUrl);
        $bindings['MissingTwitterIdCount'] = count($missingTwitterId);
        $bindings['MissingAmazonUkLink'] = count($missingAmazonUkLink);


        // Information
        $userList = $userService->getAll();
        $bindings['RegisteredUserCount'] = count($userList);


        return view('admin.index', $bindings);
    }
}
