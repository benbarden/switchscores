<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as Controller;

use App\Domain\FeaturedGame\Repository as FeaturedGameRepository;
use App\Domain\GameStats\Repository as GameStatsRepository;
use App\Domain\ViewBreadcrumbs\MainSite as Breadcrumbs;

use App\Services\Shortcode\TopRated;
use App\Services\Shortcode\Unranked;
use App\Services\Shortcode\DynamicShortcode;

use App\Traits\SwitchServices;

class NewsController extends Controller
{
    use SwitchServices;

    protected $repoFeaturedGames;
    protected $repoGameStats;
    protected $viewBreadcrumbs;

    public function __construct(
        FeaturedGameRepository $featuredGames,
        GameStatsRepository $repoGameStats,
        Breadcrumbs $viewBreadcrumbs
    )
    {
        $this->repoFeaturedGames = $featuredGames;
        $this->repoGameStats = $repoGameStats;
        $this->viewBreadcrumbs = $viewBreadcrumbs;
    }

    public function landing()
    {
        $bindings = [];

        $bindings['crumbNav'] = $this->viewBreadcrumbs->topLevelPage('News');

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

        $bindings['crumbNav'] = $this->viewBreadcrumbs->newsSubpage($categoryName);

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

        $bindings['crumbNav'] = $this->viewBreadcrumbs->newsSubpage($newsItem->title);

        // Content
        $contentHtml = $newsItem->content_html;

        $shortcodeTopRated = new TopRated($this->getServiceTopRated(), $contentHtml);
        $contentHtml = $shortcodeTopRated->parseShortcodes();

        $shortcodeUnranked = new Unranked($this->getServiceTopRated(), $contentHtml);
        $contentHtml = $shortcodeUnranked->parseShortcodes();

        $shortcodeDynamic = new DynamicShortcode($contentHtml, $this->getServiceGame());
        $contentHtml = $shortcodeDynamic->parseShortcodes();

        $bindings['NewsContentParsed'] = $contentHtml;

        // Total rank count
        $bindings['RankMaximum'] = $this->repoGameStats->totalRanked();

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
