<?php

namespace App\Http\Controllers;

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
        $request = request();
        $requestUri = $request->getPathInfo();

        $serviceNews = resolve('Services\NewsService');
        /* @var $serviceNews \App\Services\NewsService */

        $newsItem = $serviceNews->getByUrl($requestUri);
        if (!$newsItem) {
            abort(404);
        }

        $bindings = array();
        $bindings['PageTitle'] = $newsItem->title;
        $bindings['TopTitle'] = $newsItem->title;
        $bindings['NewsItem'] = $newsItem;

        // Total rank count
        $bindings['RankMaximum'] = $this->serviceGame->getListTopRatedCount();

        return view('news.content.default', $bindings);
    }
}
