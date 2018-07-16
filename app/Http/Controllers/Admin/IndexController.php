<?php

namespace App\Http\Controllers\Admin;

use App\Services\GameGenreService;
use App\Services\UserService;

class IndexController extends \App\Http\Controllers\BaseController
{
    public function feedItemsLanding()
    {
        $bindings = [];

        $bindings['TopTitle'] = 'Feed items';
        $bindings['PanelTitle'] = 'Feed items';

        return view('admin.feed-items.landing', $bindings);
    }

    public function show()
    {
        $regionCode = \Request::get('regionCode');

        $feedItemGameService = $this->serviceContainer->getFeedItemGameService();
        $feedItemReviewService = $this->serviceContainer->getFeedItemReviewService();

        $userService = resolve('Services\UserService');
        /* @var UserService $userService */
        $serviceGameGenre = resolve('Services\GameGenreService');
        /* @var $serviceGameGenre GameGenreService */

        $bindings = [];

        $bindings['TopTitle'] = 'Admin index';
        $bindings['PanelTitle'] = 'Admin index';

        // Quick stats
        $gamesWithoutDevOrPub = $this->serviceGame->getWithoutDevOrPub();
        $gamesWithoutVideos = $this->serviceGame->getWithoutVideoUrl();
        $gamesWithoutGenres = $serviceGameGenre->getGamesWithoutGenres($regionCode);
        $unprocessedFeedReviewItems = $feedItemReviewService->getUnprocessed();
        $pendingFeedGameItems = $feedItemGameService->getPending();
        $userList = $userService->getAll();

        $bindings['GamesWithoutDevOrPubCount'] = count($gamesWithoutDevOrPub);
        $bindings['GamesWithoutVideosCount'] = count($gamesWithoutVideos);
        $bindings['GamesWithoutGenresCount'] = count($gamesWithoutGenres);
        $bindings['UnprocessedFeedReviewItemsCount'] = count($unprocessedFeedReviewItems);
        $bindings['PendingFeedGameItemsCount'] = count($pendingFeedGameItems);
        $bindings['RegisteredUserCount'] = count($userList);

        return view('admin.index', $bindings);
    }
}
