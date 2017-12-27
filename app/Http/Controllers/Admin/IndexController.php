<?php

namespace App\Http\Controllers\Admin;

use App\Services\FeedItemReviewService;

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

        $gamesWithoutDevOrPub = $this->serviceGame->getWithoutDevOrPub();
        $gamesWithoutVideos = $this->serviceGame->getWithoutVideoUrl();
        $gamesWithoutGenres = $this->serviceGame->getGamesWithoutGenres();
        $unprocessedFeedItems = $feedItemReviewService->getUnprocessed();

        $bindings['GamesWithoutDevOrPubCount'] = $gamesWithoutDevOrPub->count();
        $bindings['GamesWithoutVideosCount'] = $gamesWithoutVideos->count();
        $bindings['GamesWithoutGenresCount'] = $gamesWithoutGenres->count();
        $bindings['UnprocessedFeedItemsCount'] = $unprocessedFeedItems->count();

        return view('admin.index', $bindings);
    }
}
