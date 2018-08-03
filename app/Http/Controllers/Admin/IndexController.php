<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller as Controller;
use App\Services\ServiceContainer;

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

        $feedItemGameService = $serviceContainer->getFeedItemGameService();
        $feedItemReviewService = $serviceContainer->getFeedItemReviewService();
        $userService = $serviceContainer->getUserService();
        $gameGenreService = $serviceContainer->getGameGenreService();
        $gameService = $serviceContainer->getGameService();

        $bindings = [];

        $bindings['TopTitle'] = 'Admin index';
        $bindings['PanelTitle'] = 'Admin index';


        // Feeds - Items to action
        $unprocessedFeedReviewItems = $feedItemReviewService->getUnprocessed();
        $pendingFeedGameItems = $feedItemGameService->getPending();

        $bindings['UnprocessedFeedReviewItemsCount'] = count($unprocessedFeedReviewItems);
        $bindings['PendingFeedGameItemsCount'] = count($pendingFeedGameItems);


        // Missing data
        $missingBoxart = $gameService->getWithoutBoxart($regionCode);
        $missingVendorPageUrl = $gameService->getByNullField('vendor_page_url', $regionCode);
        $missingNintendoPageUrl = $gameService->getByNullField('nintendo_page_url', $regionCode);
        $missingVideoUrl = $gameService->getByNullField('video_url', $regionCode);
        $missingTwitterId = $gameService->getByNullField('twitter_id', $regionCode);
        $missingDevOrPub = $gameService->getWithoutDevOrPub();
        $missingGenres = $gameGenreService->getGamesWithoutGenres($regionCode);
        $missingAmazonUkLink = $gameService->getWithoutAmazonUkLink();

        $bindings['MissingBoxartCount'] = count($missingBoxart);
        $bindings['MissingVendorPageUrlCount'] = count($missingVendorPageUrl);
        $bindings['MissingNintendoPageUrlCount'] = count($missingNintendoPageUrl);
        $bindings['MissingVideoUrlCount'] = count($missingVideoUrl);
        $bindings['MissingTwitterIdCount'] = count($missingTwitterId);
        $bindings['MissingDevOrPubCount'] = count($missingDevOrPub);
        $bindings['MissingGenresCount'] = count($missingGenres);
        $bindings['MissingAmazonUkLink'] = count($missingAmazonUkLink);


        // Information
        $userList = $userService->getAll();
        $bindings['RegisteredUserCount'] = count($userList);


        return view('admin.index', $bindings);
    }
}
