<?php

namespace App\Http\Controllers\Admin;

use App\Services\FeedItemReviewService;
use App\Services\UserService;

class IndexController extends \App\Http\Controllers\BaseController
{
    public function show()
    {
        $bindings = array();

        $bindings['TopTitle'] = 'Admin index';
        $bindings['PanelTitle'] = 'Admin index';

        // Quick stats
        $feedItemReviewService = resolve('Services\FeedItemReviewService');
        /* @var FeedItemReviewService $feedItemReviewService */
        $userService = resolve('Services\UserService');
        /* @var UserService $userService */

        $gamesWithoutDevOrPub = $this->serviceGame->getWithoutDevOrPub();
        $gamesWithoutVideos = $this->serviceGame->getWithoutVideoUrl();
        $gamesWithoutGenres = $this->serviceGame->getGamesWithoutGenres();
        $unprocessedFeedItems = $feedItemReviewService->getUnprocessed();
        $userList = $userService->getAll();

        $bindings['GamesWithoutDevOrPubCount'] = $gamesWithoutDevOrPub->count();
        $bindings['GamesWithoutVideosCount'] = $gamesWithoutVideos->count();
        $bindings['GamesWithoutGenresCount'] = $gamesWithoutGenres->count();
        $bindings['UnprocessedFeedItemsCount'] = $unprocessedFeedItems->count();
        $bindings['RegisteredUserCount'] = $userList->count();

        return view('admin.index', $bindings);
    }
}
