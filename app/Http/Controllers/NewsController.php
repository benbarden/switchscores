<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as Controller;

use App\Services\ServiceContainer;

class NewsController extends BaseController
{
    public function landing()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $bindings = [];

        $serviceNews = $serviceContainer->getNewsService();

        $newsList = $serviceNews->getPaginated(10);

        $bindings['NewsList'] = $newsList;
        $bindings['TopTitle'] = 'News - page '.$newsList->currentPage();
        $bindings['PageTitle'] = 'News - page '.$newsList->currentPage();

        return view('news.landing', $bindings);
    }

    public function displayContent()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $regionCode = \Request::get('regionCode');

        $serviceTopRated = $serviceContainer->getTopRatedService();
        $serviceNews = $serviceContainer->getNewsService();
        $serviceGameReleaseDate = $serviceContainer->getGameReleaseDateService();

        $request = request();
        $requestUri = $request->getPathInfo();

        $newsItem = $serviceNews->getByUrl($requestUri);
        if (!$newsItem) {
            abort(404);
        }

        $bindings = [];
        $bindings['PageTitle'] = $newsItem->title;
        $bindings['TopTitle'] = $newsItem->title;
        $bindings['NewsItem'] = $newsItem;

        // Total rank count
        $bindings['RankMaximum'] = $serviceTopRated->getCount($regionCode);

        // Next/Previous links
        $newsNext = $serviceNews->getNext($newsItem);
        $newsPrev = $serviceNews->getPrevious($newsItem);
        if ($newsNext) {
            $bindings['NewsNext'] = $newsNext;
        }
        if ($newsPrev) {
            $bindings['NewsPrev'] = $newsPrev;
        }

        // Game details
        if ($newsItem->game_id) {
            $bindings['ReleaseDateInfo'] = $serviceGameReleaseDate->getByGameAndRegion($newsItem->game_id, $regionCode);
        }

        return view('news.content.default', $bindings);
    }
}
