<?php

namespace App\Http\Controllers;

use Carbon\Carbon;

class NewsController extends BaseController
{
    public function landing()
    {
        $bindings = array();

        $bindings['TopTitle'] = 'News';
        $bindings['PageTitle'] = 'News';

        $serviceNews = resolve('Services\NewsService');
        /* @var $serviceNews \App\Services\NewsService */
        $bindings['NewsList'] = $serviceNews->getAllWithLimit(10);

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

        return view('news.content.default', $bindings);
    }
}
