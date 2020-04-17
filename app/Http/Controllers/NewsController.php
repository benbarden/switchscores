<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;

class NewsController extends Controller
{
    use SwitchServices;

    public function landing()
    {
        $bindings = [];

        $newsList = $this->getServiceNews()->getPaginated(12);

        $bindings['NewsList'] = $newsList;
        $bindings['TopTitle'] = 'News - page '.$newsList->currentPage();
        $bindings['PageTitle'] = 'News - page '.$newsList->currentPage();

        $bindings['DisplayMode'] = 'home';

        return view('news.tiled-layout', $bindings);
    }

    public function categoryLanding($linkName)
    {
        $bindings = [];

        $category = $this->getServiceNewsCategory()->getByUrl($linkName);
        if (!$category) abort(404);

        $categoryId = $category->id;
        $categoryName = $category->name;

        $newsList = $this->getServiceNews()->getPaginatedByCategory($categoryId, 12);

        $bindings['NewsList'] = $newsList;
        $bindings['TopTitle'] = $categoryName.' - page '.$newsList->currentPage();
        $bindings['PageTitle'] = $categoryName.' - page '.$newsList->currentPage();

        $bindings['DisplayMode'] = 'category';

        return view('news.tiled-layout', $bindings);
    }

    public function displayContent()
    {
        $serviceNews = $this->getServiceNews();
        $serviceGame = $this->getServiceGame();

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
        $bindings['RankMaximum'] = $serviceGame->countRanked();

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
            $gameId = $newsItem->game_id;
            $bindings['GameDevelopers'] = $this->getServiceGameDeveloper()->getByGame($gameId);
            $bindings['GamePublishers'] = $this->getServiceGamePublisher()->getByGame($gameId);
            $bindings['GameTags'] = $this->getServiceGameTag()->getByGame($gameId);
        }

        // Category links
        $bindings['CategoryList'] = $this->getServiceNewsCategory()->getAll();

        return view('news.content.default', $bindings);
    }
}
