<?php

namespace App\Domain\Sitemap;

use App\Domain\GameCalendar\Repository as GameCalendarRepository;
use App\Domain\ReviewSite\Repository as ReviewSiteRepository;
use App\Domain\Category\Repository as CategoryRepository;
use App\Domain\GameCollection\Repository as CollectionRepository;
use App\Domain\GameSeries\Repository as GameSeriesRepository;
use App\Domain\Tag\Repository as TagRepository;
use App\Domain\GameCalendar\AllowedDates;
use App\Models\Console;
use App\Domain\Console\Repository as ConsoleRepository;
use App\Domain\News\Repository as NewsRepository;

class Generator
{
    const VIEW_STANDARD = 'sitemap.standard';
    const VIEW_INDEX = 'sitemap.index';
    const VIEW_GAMES = 'sitemap.games';

    const SITEMAP_INDEX = 'index.xml';
    const SITEMAP_SITE = 'sitemap-site.xml';
    const SITEMAP_NEWS = 'sitemap-news.xml';
    const SITEMAP_GAMES = 'sitemap-games-[CONSOLE]-[YEAR].xml';
    const SITEMAP_CALENDAR = 'sitemap-calendar.xml';
    const SITEMAP_TOP_RATED = 'sitemap-top-rated.xml';
    const SITEMAP_REVIEW_PARTNERS = 'sitemap-review-partners.xml';
    const SITEMAP_CATEGORY = 'sitemap-category.xml';
    const SITEMAP_COLLECTION = 'sitemap-collection.xml';
    const SITEMAP_SERIES = 'sitemap-series.xml';
    const SITEMAP_TAG = 'sitemap-tag.xml';

    public function __construct(
        private GameCalendarRepository $repoGameCalendar
    )
    {

    }

    public function getTimestampNow(): string
    {
        $now = new \DateTime('now');
        $timestamp = $now->format('c');
        return $timestamp;
    }

    public function getBindings(): array
    {
        $bindings = [];
        $timestamp = $this->getTimestampNow();
        $bindings['TimestampNow'] = $timestamp;
        return $bindings;
    }

    public function saveToXml($viewFile, $bindings, $xmlFile): void
    {
        $xmlOutput = response()->view($viewFile, $bindings)->content();
        file_put_contents(public_path().'/sitemaps/'.$xmlFile, $xmlOutput);
    }

    public function generateIndex(): void
    {
        $bindings = $this->getBindings();
        $timestamp = $this->getTimestampNow();

        $allowedDates = new AllowedDates();
        $yearListS1 = $allowedDates->releaseYearsByConsole(Console::ID_SWITCH_1);
        $yearListS2 = $allowedDates->releaseYearsByConsole(Console::ID_SWITCH_2);

        $indexList = [];
        $indexList[] = ['XmlFile' => '/sitemaps/'.self::SITEMAP_SITE, 'Timestamp' => $timestamp];
        foreach ($yearListS1 as $year) {
            $xmlFile = str_replace('[YEAR]', $year, '/sitemaps/'.self::SITEMAP_GAMES);
            $xmlFile = str_replace('[CONSOLE]', 'switch-1', $xmlFile);
            $indexList[] = ['XmlFile' => $xmlFile, 'Timestamp' => $timestamp];
        }
        foreach ($yearListS2 as $year) {
            $xmlFile = str_replace('[YEAR]', $year, '/sitemaps/'.self::SITEMAP_GAMES);
            $xmlFile = str_replace('[CONSOLE]', 'switch-2', $xmlFile);
            $indexList[] = ['XmlFile' => $xmlFile, 'Timestamp' => $timestamp];
        }
        $indexList[] = ['XmlFile' => '/sitemaps/'.self::SITEMAP_TOP_RATED, 'Timestamp' => $timestamp];
        $indexList[] = ['XmlFile' => '/sitemaps/'.self::SITEMAP_REVIEW_PARTNERS, 'Timestamp' => $timestamp];
        $indexList[] = ['XmlFile' => '/sitemaps/'.self::SITEMAP_CALENDAR, 'Timestamp' => $timestamp];
        $indexList[] = ['XmlFile' => '/sitemaps/'.self::SITEMAP_CATEGORY, 'Timestamp' => $timestamp];
        $indexList[] = ['XmlFile' => '/sitemaps/'.self::SITEMAP_COLLECTION, 'Timestamp' => $timestamp];
        $indexList[] = ['XmlFile' => '/sitemaps/'.self::SITEMAP_SERIES, 'Timestamp' => $timestamp];
        $indexList[] = ['XmlFile' => '/sitemaps/'.self::SITEMAP_TAG, 'Timestamp' => $timestamp];

        $bindings['IndexList'] = $indexList;

        $this->saveToXml(self::VIEW_INDEX, $bindings, self::SITEMAP_INDEX);
    }

    public function generateSite(): void
    {
        $bindings = $this->getBindings();
        $timestamp = $this->getTimestampNow();

        $sitemapPages = [];
        $sitemapPages[] = array('url' => route('welcome'), 'lastmod' => $timestamp, 'changefreq' => 'daily', 'priority' => '1.0');

        $repoConsole = new ConsoleRepository();
        $consoleList = $repoConsole->getAll();
        foreach ($consoleList as $console) {
            // Landing
            $sitemapPages[] = array('url' => route('console.landing', ['console' => $console]), 'lastmod' => $timestamp, 'changefreq' => 'daily', 'priority' => '0.9');
            // Pages
            $sitemapPages[] = array('url' => route('console.byCategory.landing', ['console' => $console]), 'lastmod' => $timestamp, 'changefreq' => 'daily', 'priority' => '0.9');
            $sitemapPages[] = array('url' => route('console.bySeries.landing', ['console' => $console]), 'lastmod' => $timestamp, 'changefreq' => 'daily', 'priority' => '0.9');
            $sitemapPages[] = array('url' => route('console.byTag.landing', ['console' => $console]), 'lastmod' => $timestamp, 'changefreq' => 'daily', 'priority' => '0.9');
            $sitemapPages[] = array('url' => route('console.byCollection.landing', ['console' => $console]), 'lastmod' => $timestamp, 'changefreq' => 'daily', 'priority' => '0.9');
        }

        //$sitemapPages[] = array('url' => route('games.browse.byDate.landing'), 'lastmod' => $timestamp, 'changefreq' => 'daily', 'priority' => '0.9');
        $sitemapPages[] = array('url' => route('games.recentReleases'), 'lastmod' => $timestamp, 'changefreq' => 'weekly', 'priority' => '0.8');
        $sitemapPages[] = array('url' => route('games.upcomingReleases'), 'lastmod' => $timestamp, 'changefreq' => 'weekly', 'priority' => '0.7');
        $sitemapPages[] = array('url' => route('games.onSale'), 'lastmod' => $timestamp, 'changefreq' => 'daily', 'priority' => '0.5');

        $bindings['SitemapPages'] = $sitemapPages;

        $this->saveToXml(self::VIEW_STANDARD, $bindings, self::SITEMAP_SITE);
    }

    public function generateNews(): void
    {
        $bindings = $this->getBindings();
        $timestamp = $this->getTimestampNow();

        $sitemapPages = [];
        $sitemapPages[] = array('url' => route('news.landing'), 'lastmod' => $timestamp, 'changefreq' => 'weekly', 'priority' => '0.8');

        $repoNews = new NewsRepository();
        $newsList = $repoNews->getAll();
        foreach ($newsList as $newsItem) {
            $lastMod = new \DateTime($newsItem->updated_at);
            $lastModTimestamp = $lastMod->format('c');
            $sitemapPages[] = array(
                'url' => url($newsItem->url),
                'lastmod' => $lastModTimestamp,
                'changefreq' => 'monthly',
                'priority' => '0.7');
        }

        $bindings['SitemapPages'] = $sitemapPages;

        $this->saveToXml(self::VIEW_STANDARD, $bindings, self::SITEMAP_NEWS);
    }

    public function generateGames(): void
    {
        $allowedDates = new AllowedDates();

        $yearListS1 = $allowedDates->releaseYearsByConsole(Console::ID_SWITCH_1);
        $yearListS2 = $allowedDates->releaseYearsByConsole(Console::ID_SWITCH_2);

        foreach ($yearListS1 as $year) {

            $gameList = $this->repoGameCalendar->byYear(Console::ID_SWITCH_1, $year, false);
            $bindings = $this->getBindings();
            $bindings['GameList'] = $gameList;
            $xmlFile = str_replace('[YEAR]', $year, self::SITEMAP_GAMES);
            $xmlFile = str_replace('[CONSOLE]', 'switch-1', $xmlFile);
            $this->saveToXml(self::VIEW_GAMES, $bindings, $xmlFile);

        }

        foreach ($yearListS2 as $year) {

            $gameList = $this->repoGameCalendar->byYear(Console::ID_SWITCH_2, $year, false);
            $bindings = $this->getBindings();
            $bindings['GameList'] = $gameList;
            $xmlFile = str_replace('[YEAR]', $year, self::SITEMAP_GAMES);
            $xmlFile = str_replace('[CONSOLE]', 'switch-2', $xmlFile);
            $this->saveToXml(self::VIEW_GAMES, $bindings, $xmlFile);

        }
    }

    public function generateTopRated(): void
    {
        $allowedDates = new AllowedDates();
        $repoConsole = new ConsoleRepository();

        $bindings = $this->getBindings();
        $timestamp = $this->getTimestampNow();

        $sitemapPages = [];

        $consoleList = $repoConsole->getAll();
        foreach ($consoleList as $console) {

            $sitemapPages[] = ['url' => route('console.topRated.landing', ['console' => $console]), 'lastmod' => $timestamp, 'changefreq' => 'weekly', 'priority' => '0.8'];
            $sitemapPages[] = ['url' => route('console.topRated.allTime', ['console' => $console]), 'lastmod' => $timestamp, 'changefreq' => 'weekly', 'priority' => '0.8'];

            $yearList = $allowedDates->releaseYearsByConsole($console->id);

            foreach ($yearList as $year) {
                $sitemapPages[] = ['url' => route('console.topRated.byYear', ['console' => $console, 'year' => $year]), 'lastmod' => $timestamp, 'changefreq' => 'weekly', 'priority' => '0.8'];
            }

        }

        $bindings['SitemapPages'] = $sitemapPages;

        $this->saveToXml(self::VIEW_STANDARD, $bindings, self::SITEMAP_TOP_RATED);
    }

    public function generateReviewPartners(): void
    {
        $bindings = $this->getBindings();
        $timestamp = $this->getTimestampNow();

        $sitemapPages = [];

        $sitemapPages[] = ['url' => route('reviews.landing'), 'lastmod' => $timestamp, 'changefreq' => 'weekly', 'priority' => '0.8'];

        $repoReviewSite = new ReviewSiteRepository();

        $reviewSiteList = $repoReviewSite->getActive();
        foreach ($reviewSiteList as $reviewSite) {
            $sitemapPages[] = ['url' => route('partners.review-sites.siteProfile', ['linkTitle' => $reviewSite->link_title]), 'lastmod' => $timestamp, 'changefreq' => 'weekly', 'priority' => '0.8'];
        }

        $bindings['SitemapPages'] = $sitemapPages;

        $this->saveToXml(self::VIEW_STANDARD, $bindings, self::SITEMAP_REVIEW_PARTNERS);
    }

    public function generateCalendar(): void
    {
        $bindings = $this->getBindings();
        $timestamp = $this->getTimestampNow();

        $sitemapPages = [];

        $repoConsole = new ConsoleRepository();
        $allowedDates = new AllowedDates();
        $consoleList = $repoConsole->getAll();
        foreach ($consoleList as $console) {
            $years = $allowedDates->releaseYearsByConsole($console->id);
            foreach ($years as $year) {
                $sitemapPages[] = array('url' => route('console.byYear', ['console' => $console, 'year' => $year]), 'lastmod' => $timestamp, 'changefreq' => 'weekly', 'priority' => '0.8');
            }
        }

        $bindings['SitemapPages'] = $sitemapPages;

        $this->saveToXml(self::VIEW_STANDARD, $bindings, self::SITEMAP_CALENDAR);
    }

    public function generateCategory(): void
    {
        $repoCategory = new CategoryRepository();

        $bindings = $this->getBindings();
        $timestamp = $this->getTimestampNow();

        $sitemapPages = [];

        $repoConsole = new ConsoleRepository();
        $consoleList = $repoConsole->getAll();
        $categoryList = $repoCategory->getAll();

        foreach ($consoleList as $console) {

            $sitemapPages[] = ['url' => route('console.byCategory.landing', ['console' => $console]), 'lastmod' => $timestamp, 'changefreq' => 'weekly', 'priority' => '0.8'];

            foreach ($categoryList as $item) {
                $sitemapPages[] = ['url' => route('console.byCategory.page', ['console' => $console, 'category' => $item->link_title]), 'lastmod' => $timestamp, 'changefreq' => 'weekly', 'priority' => '0.8'];
            }

        }

        $bindings['SitemapPages'] = $sitemapPages;

        $this->saveToXml(self::VIEW_STANDARD, $bindings, self::SITEMAP_CATEGORY);
    }

    public function generateCollection(): void
    {
        $repoCollection = new CollectionRepository();

        $bindings = $this->getBindings();
        $timestamp = $this->getTimestampNow();

        $sitemapPages = [];

        $repoConsole = new ConsoleRepository();
        $consoleList = $repoConsole->getAll();
        $collectionList = $repoCollection->getAll();

        foreach ($consoleList as $console) {

            $sitemapPages[] = ['url' => route('console.byCollection.landing', ['console' => $console]), 'lastmod' => $timestamp, 'changefreq' => 'weekly', 'priority' => '0.8'];

            foreach ($collectionList as $item) {
                $sitemapPages[] = ['url' => route('console.byCollection.page', ['console' => $console, 'collection' => $item->link_title]), 'lastmod' => $timestamp, 'changefreq' => 'weekly', 'priority' => '0.8'];
            }

        }

        $bindings['SitemapPages'] = $sitemapPages;

        $this->saveToXml(self::VIEW_STANDARD, $bindings, self::SITEMAP_COLLECTION);
    }

    public function generateSeries(): void
    {
        $repoSeries = new GameSeriesRepository();

        $bindings = $this->getBindings();
        $timestamp = $this->getTimestampNow();

        $sitemapPages = [];

        $repoConsole = new ConsoleRepository();
        $consoleList = $repoConsole->getAll();
        $seriesList = $repoSeries->getAll();

        foreach ($consoleList as $console) {

            $sitemapPages[] = ['url' => route('console.bySeries.landing', ['console' => $console]), 'lastmod' => $timestamp, 'changefreq' => 'weekly', 'priority' => '0.8'];

            foreach ($seriesList as $item) {
                $sitemapPages[] = ['url' => route('console.bySeries.page', ['console' => $console, 'series' => $item->link_title]), 'lastmod' => $timestamp, 'changefreq' => 'weekly', 'priority' => '0.8'];
            }

        }

        $bindings['SitemapPages'] = $sitemapPages;

        $this->saveToXml(self::VIEW_STANDARD, $bindings, self::SITEMAP_SERIES);
    }

    public function generateTag(): void
    {
        $repoTag = new TagRepository();

        $bindings = $this->getBindings();
        $timestamp = $this->getTimestampNow();

        $sitemapPages = [];

        $repoConsole = new ConsoleRepository();
        $consoleList = $repoConsole->getAll();
        $tagList = $repoTag->getAll();

        foreach ($consoleList as $console) {

            $sitemapPages[] = ['url' => route('console.byTag.landing', ['console' => $console]), 'lastmod' => $timestamp, 'changefreq' => 'weekly', 'priority' => '0.8'];

            foreach ($tagList as $item) {
                $sitemapPages[] = ['url' => route('console.byTag.page', ['console' => $console, 'tag' => $item->link_title]), 'lastmod' => $timestamp, 'changefreq' => 'weekly', 'priority' => '0.8'];
            }

        }

        $bindings['SitemapPages'] = $sitemapPages;

        $this->saveToXml(self::VIEW_STANDARD, $bindings, self::SITEMAP_TAG);
    }
}