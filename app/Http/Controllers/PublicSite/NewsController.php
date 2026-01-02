<?php

namespace App\Http\Controllers\PublicSite;

use Illuminate\Routing\Controller as Controller;

use App\Domain\View\Breadcrumbs\PublicBreadcrumbs;
use App\Domain\View\PageBuilders\PublicPageBuilder;

use App\Domain\GameCalendar\AllowedDates;
use App\Domain\GameDeveloper\Repository as GameDeveloperRepository;
use App\Domain\GameLists\Repository as GameListsRepository;
use App\Domain\GamePublisher\Repository as GamePublisherRepository;
use App\Domain\GameStats\Repository as GameStatsRepository;
use App\Domain\NewsDbUpdate\Repository as NewsDbUpdateRepository;
use App\Domain\News\Repository as NewsRepository;
use App\Domain\NewsCategory\Repository as NewsCategoryRepository;
use App\Domain\GameTag\Repository as GameTagRepository;

use App\Domain\Shortcode\DynamicShortcode;
use App\Domain\Shortcode\TopRated;
use App\Domain\Shortcode\Unranked;

class NewsController extends Controller
{
    public function __construct(
        private PublicPageBuilder $pageBuilder,
        private GameListsRepository $repoGameLists,
        private GameStatsRepository $repoGameStats,
        private AllowedDates $allowedDates,
        private NewsDbUpdateRepository $repoNewsDbUpdate,
        private NewsRepository $repoNews,
        private NewsCategoryRepository $repoNewsCategory,
        private GamePublisherRepository $repoGamePublisher,
        private GameDeveloperRepository $repoGameDeveloper,
        private GameTagRepository $repoGameTag
    )
    {
    }

    public function landing()
    {
        $newsList = $this->repoNews->getPaginated(12);

        $pageTitle = 'News - page '.$newsList->currentPage();
        $bindings = $this->pageBuilder->build($pageTitle, PublicBreadcrumbs::topLevel($pageTitle))->bindings;

        $bindings['NewsList'] = $newsList;

        $bindings['MetaDescription'] = 'The latest from Switch Scores — weekly roundups, top-rated lists, and feature updates from the Nintendo Switch community.';

        $bindings['DisplayMode'] = 'home';

        return view('public.news.tiled-layout', $bindings);
    }

    public function databaseUpdatesLanding()
    {
        $pageTitle = 'Database updates';
        $bindings = $this->pageBuilder->build($pageTitle, PublicBreadcrumbs::newsSubpage($pageTitle))->bindings;

        $allowedYears = $this->allowedDates->releaseYears();
        $bindings['AllowedYears'] = $allowedYears;
        foreach ($allowedYears as $year) {
            $newsDbUpdateList = $this->repoNewsDbUpdate->getAllByYear($year, true);
            if ($newsDbUpdateList) {
                $bindings['NewsDbUpdateList'.$year] = $newsDbUpdateList;
            }
        }

        return view('public.news.database-updates-landing', $bindings);
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

        $pageTitle = 'Database updates: '.$year.', week '.$week;
        $bindings = $this->pageBuilder->build($pageTitle, PublicBreadcrumbs::newsSubpage($pageTitle))->bindings;

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

    public function categoryLanding($linkName)
    {
        $category = $this->repoNewsCategory->byUrl($linkName);
        if (!$category) abort(404);

        $categoryId = $category->id;
        $categoryName = $category->name;

        $newsList = $this->repoNews->getPaginatedByCategory($categoryId, 12);

        $pageTitle = $categoryName.' - page '.$newsList->currentPage();
        $bindings = $this->pageBuilder->build($pageTitle, PublicBreadcrumbs::newsSubpage($pageTitle))->bindings;

        $bindings['NewsList'] = $newsList;

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

        $pageTitle = $newsItem->title;
        $bindings = $this->pageBuilder->build($pageTitle, PublicBreadcrumbs::newsSubpage($pageTitle))->bindings;

        $bindings['NewsItem'] = $newsItem;

        // Content
        $contentHtml = $newsItem->content_html;

        $shortcodeTopRated = new TopRated($contentHtml);
        $contentHtml = $shortcodeTopRated->parseShortcodes();

        $shortcodeUnranked = new Unranked($contentHtml);
        $contentHtml = $shortcodeUnranked->parseShortcodes();

        $shortcodeDynamic = new DynamicShortcode($contentHtml);
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
            $bindings['GameTags'] = $this->repoGameTag->getGameTags($gameId);
        }

        // Category links
        $bindings['CategoryList'] = $this->repoNewsCategory->getAll();

        return view('public.news.content.default', $bindings);
    }
}
