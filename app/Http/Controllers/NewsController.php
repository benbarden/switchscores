<?php

namespace App\Http\Controllers;

use App\Services\GameReleaseDateService;
use App\Services\TopRatedService;
use Carbon\Carbon;

class NewsController extends BaseController
{
    public function landing()
    {
        $bindings = array();

        $serviceNews = resolve('Services\NewsService');
        /* @var $serviceNews \App\Services\NewsService */
        $newsList = $serviceNews->getPaginated(10);

        $bindings['NewsList'] = $newsList;
        $bindings['TopTitle'] = 'News - page '.$newsList->currentPage();
        $bindings['PageTitle'] = 'News - page '.$newsList->currentPage();

        return view('news.landing', $bindings);
    }

    public function displayContent($date, $title)
    {
        $regionCode = \Request::get('regionCode');

        $serviceTopRated = resolve('Services\TopRatedService');
        /* @var $serviceTopRated TopRatedService */
        $serviceNews = resolve('Services\NewsService');
        /* @var $serviceNews \App\Services\NewsService */
        $serviceGameReleaseDate = resolve('Services\GameReleaseDateService');
        /* @var $serviceGameReleaseDate GameReleaseDateService */

        $request = request();
        $requestUri = $request->getPathInfo();

        $newsItem = $serviceNews->getByUrl($requestUri);
        if (!$newsItem) {
            abort(404);
        }

        $bindings = array();
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
