<?php

namespace App\Http\Controllers\PublicSite;

use App\Domain\FeaturedGame\Repository as FeaturedGameRepository;
use App\Domain\GameCalendar\AllowedDates;
use App\Domain\GameDeveloper\Repository as GameDeveloperRepository;
use App\Domain\GameLists\Repository as GameListsRepository;
use App\Domain\GamePublisher\Repository as GamePublisherRepository;
use App\Domain\GameStats\Repository as GameStatsRepository;
use App\Domain\ViewBreadcrumbs\MainSite as Breadcrumbs;
use App\Domain\NewsDbUpdate\Repository as NewsDbUpdateRepository;
use App\Domain\News\Repository as NewsRepository;
use App\Domain\NewsCategory\Repository as NewsCategoryRepository;

use App\Services\Shortcode\DynamicShortcode;
use App\Services\Shortcode\TopRated;
use App\Services\Shortcode\Unranked;

use App\Traits\SwitchServices;

use Illuminate\Routing\Controller as Controller;

class NewsController extends Controller
{
    use SwitchServices;

    public function __construct(
        private FeaturedGameRepository $repoFeaturedGames,
        private GameListsRepository $repoGameLists,
        private GameStatsRepository $repoGameStats,
        private AllowedDates $allowedDates,
        private Breadcrumbs $viewBreadcrumbs,
        private NewsDbUpdateRepository $repoNewsDbUpdate,
        private NewsRepository $repoNews,
        private NewsCategoryRepository $repoNewsCategory,
        private GamePublisherRepository $repoGamePublisher,
        private GameDeveloperRepository $repoGameDeveloper
    )
    {
    }

    public function landing()
    {
        $bindings = [];

        $pageTitle = 'News';

        $bindings['TopTitle'] = $pageTitle;
        $bindings['PageTitle'] = $pageTitle;

        $allowedYears = $this->allowedDates->releaseYears();
        $bindings['AllowedYears'] = array_reverse($allowedYears);
        foreach ($allowedYears as $year) {
            $newsDbUpdateList = $this->repoNewsDbUpdate->getAllByYear($year, true);
            if ($newsDbUpdateList) {
                $bindings['NewsDbUpdateList'.$year] = $newsDbUpdateList;
            }
        }

        return view('public.news.landing', $bindings);
    }

    public function databaseUpdates($year, $week)
    {
        /*
        if ($week < 1 || $week > 53) abort(404);

        if (!in_array($year, $this->allowedDates->getAllowedYears())) abort(404);

        if ($year == 2017) {
            if ($week < NewsDbUpdate::SWITCH_LAUNCH_WEEK_2017) abort (404);
        }
        */

        $bindings = [];

        $pageTitle = 'Database updates: '.$year.', week '.$week;
        $bindings['crumbNav'] = $this->viewBreadcrumbs->newsSubpage($pageTitle);

        $bindings['TopTitle'] = $pageTitle;
        $bindings['PageTitle'] = $pageTitle;

        $newsDbUpdate = $this->repoNewsDbUpdate->get($year, $week);
        if (!$newsDbUpdate) abort(404);

        $bindings['NewsDbUpdate'] = $newsDbUpdate;

        $gameListStandard = $this->repoGameLists->byYearWeek($year, $week, false);
        $gameListLowQuality = $this->repoGameLists->byYearWeek($year, $week, true);

        if (count($gameListStandard) == 0 && count($gameListLowQuality) == 0) abort(404);

        $bindings['GameListStandard'] = $gameListStandard;
        $bindings['GameListLowQuality'] = $gameListLowQuality;

        return view('public.news.database-updates', $bindings);
    }

    public function landingArchive()
    {
        $bindings = [];

        $bindings['crumbNav'] = $this->viewBreadcrumbs->newsSubpage('Archive');

        $newsList = $this->repoNews->getPaginated(12);

        $bindings['NewsList'] = $newsList;
        $bindings['TopTitle'] = 'News - page '.$newsList->currentPage();
        $bindings['PageTitle'] = 'News - page '.$newsList->currentPage();

        $bindings['DisplayMode'] = 'home';

        return view('public.news.tiled-layout', $bindings);
    }

    public function categoryLanding($linkName)
    {
        $bindings = [];

        $category = $this->repoNewsCategory->byUrl($linkName);
        if (!$category) abort(404);

        $categoryId = $category->id;
        $categoryName = $category->name;

        $bindings['crumbNav'] = $this->viewBreadcrumbs->newsSubpage($categoryName);

        $newsList = $this->repoNews->getPaginatedByCategory($categoryId, 12);

        $bindings['NewsList'] = $newsList;
        $bindings['TopTitle'] = $categoryName.' - page '.$newsList->currentPage();
        $bindings['PageTitle'] = $categoryName.' - page '.$newsList->currentPage();

        $bindings['DisplayMode'] = 'category';

        return view('public.news.tiled-layout', $bindings);
    }

    public function displayContent()
    {
        $request = request();
        $requestUri = $request->getPathInfo();

        $newsItem = $this->repoNews->getByUrl($requestUri);
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
        $newsNext = $this->repoNews->getNext($newsItem);
        $newsPrev = $this->repoNews->getPrevious($newsItem);
        if ($newsNext) {
            $bindings['NewsNext'] = $newsNext;
        }
        if ($newsPrev) {
            $bindings['NewsPrev'] = $newsPrev;
        }

        // Game details
        if ($newsItem->game_id) {
            $gameId = $newsItem->game_id;
            $bindings['GameDevelopers'] = $this->repoGameDeveloper->byGame($gameId);
            $bindings['GamePublishers'] = $this->repoGamePublisher->byGame($gameId);
            $bindings['GameTags'] = $this->getServiceGameTag()->getByGame($gameId);
        }

        // Category links
        $bindings['CategoryList'] = $this->repoNewsCategory->getAll();

        return view('public.news.content.default', $bindings);
    }
}
