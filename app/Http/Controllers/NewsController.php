<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SiteRequestData;
use App\Traits\WosServices;

class NewsController extends Controller
{
    use SiteRequestData;
    use WosServices;

    public function landing()
    {
        $bindings = [];

        $serviceNews = $this->getServiceNews();

        $newsList = $serviceNews->getPaginated(10);

        $bindings['NewsList'] = $newsList;
        $bindings['TopTitle'] = 'News - page '.$newsList->currentPage();
        $bindings['PageTitle'] = 'News - page '.$newsList->currentPage();

        return view('news.landing', $bindings);
    }

    public function displayContent()
    {
        $regionCode = $this->getRegionCode();

        $serviceNews = $this->getServiceNews();
        $serviceGameRankAllTime = $this->getServiceGameRankAllTime();
        $serviceGameReleaseDate = $this->getServiceGameReleaseDate();

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
        $bindings['RankMaximum'] = $serviceGameRankAllTime->countRanked();

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
