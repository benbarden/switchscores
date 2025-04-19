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

class Generator
{
    const VIEW_STANDARD = 'sitemap.standard';
    const VIEW_INDEX = 'sitemap.index';
    const VIEW_GAMES = 'sitemap.games';

    const SITEMAP_INDEX = 'index.xml';
    const SITEMAP_SITE = 'sitemap-site.xml';
    const SITEMAP_GAMES = 'sitemap-games-[CONSOLE]-[YEAR].xml';
    const SITEMAP_CALENDAR = 'sitemap-calendar.xml';
    const SITEMAP_TOP_RATED = 'sitemap-top-rated.xml';
    const SITEMAP_REVIEW_STATS = 'sitemap-review-stats.xml';
    const SITEMAP_REVIEW_PARTNERS = 'sitemap-review-partners.xml';
    const SITEMAP_CATEGORY = 'sitemap-category.xml';
    const SITEMAP_COLLECTION = 'sitemap-collection.xml';
    const SITEMAP_SERIES = 'sitemap-series.xml';
    const SITEMAP_TAG = 'sitemap-tag.xml';

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
        $indexList[] = ['XmlFile' => '/sitemaps/'.self::SITEMAP_REVIEW_STATS, 'Timestamp' => $timestamp];
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
        $sitemapPages[] = array('url' => route('games.landing'), 'lastmod' => $timestamp, 'changefreq' => 'daily', 'priority' => '0.9');
        $sitemapPages[] = array('url' => route('games.browse.byCategory.landing'), 'lastmod' => $timestamp, 'changefreq' => 'daily', 'priority' => '0.9');
        $sitemapPages[] = array('url' => route('games.browse.bySeries.landing'), 'lastmod' => $timestamp, 'changefreq' => 'daily', 'priority' => '0.9');
        $sitemapPages[] = array('url' => route('games.browse.byTag.landing'), 'lastmod' => $timestamp, 'changefreq' => 'daily', 'priority' => '0.9');
        $sitemapPages[] = array('url' => route('games.browse.byCollection.landing'), 'lastmod' => $timestamp, 'changefreq' => 'daily', 'priority' => '0.9');
        $sitemapPages[] = array('url' => route('games.browse.byDate.landing'), 'lastmod' => $timestamp, 'changefreq' => 'daily', 'priority' => '0.9');
        $sitemapPages[] = array('url' => route('games.onSale'), 'lastmod' => $timestamp, 'changefreq' => 'daily', 'priority' => '0.9');
        $sitemapPages[] = array('url' => route('games.recentReleases'), 'lastmod' => $timestamp, 'changefreq' => 'weekly', 'priority' => '0.8');
        $sitemapPages[] = array('url' => route('games.upcomingReleases'), 'lastmod' => $timestamp, 'changefreq' => 'weekly', 'priority' => '0.8');
        $sitemapPages[] = array('url' => route('news.landing'), 'lastmod' => $timestamp, 'changefreq' => 'weekly', 'priority' => '0.8');

        $bindings['SitemapPages'] = $sitemapPages;

        $this->saveToXml(self::VIEW_STANDARD, $bindings, self::SITEMAP_SITE);
    }

    public function generateGames(): void
    {
        $repoGameCalendar = new GameCalendarRepository();
        $allowedDates = new AllowedDates();

        $yearListS1 = $allowedDates->releaseYearsByConsole(Console::ID_SWITCH_1);
        $yearListS2 = $allowedDates->releaseYearsByConsole(Console::ID_SWITCH_2);

        foreach ($yearListS1 as $year) {

            $gameList = $repoGameCalendar->byYear(Console::ID_SWITCH_1, $year);
            $bindings = $this->getBindings();
            $bindings['GameList'] = $gameList;
            $xmlFile = str_replace('[YEAR]', $year, self::SITEMAP_GAMES);
            $xmlFile = str_replace('[CONSOLE]', 'switch-1', $xmlFile);
            $this->saveToXml(self::VIEW_GAMES, $bindings, $xmlFile);

        }

        foreach ($yearListS2 as $year) {

            $gameList = $repoGameCalendar->byYear(Console::ID_SWITCH_2, $year);
            $bindings = $this->getBindings();
            $bindings['GameList'] = $gameList;
            $xmlFile = str_replace('[YEAR]', $year, self::SITEMAP_GAMES);
            $xmlFile = str_replace('[CONSOLE]', 'switch-2', $xmlFile);
            $this->saveToXml(self::VIEW_GAMES, $bindings, $xmlFile);

        }
    }

    public function generateTopRated(): void
    {
        $bindings = $this->getBindings();
        $timestamp = $this->getTimestampNow();

        $sitemapPages = [];

        $sitemapPages[] = ['url' => route('topRated.landing'), 'lastmod' => $timestamp, 'changefreq' => 'weekly', 'priority' => '0.8'];
        $sitemapPages[] = ['url' => route('topRated.allTime'), 'lastmod' => $timestamp, 'changefreq' => 'weekly', 'priority' => '0.8'];

        $allowedDates = new AllowedDates();

        $yearListS1 = $allowedDates->releaseYearsByConsole(Console::ID_SWITCH_1);

        foreach ($yearListS1 as $year) {
            $sitemapPages[] = ['url' => route('topRated.byYear', ['year' => $year]), 'lastmod' => $timestamp, 'changefreq' => 'weekly', 'priority' => '0.8'];
        }

        $bindings['SitemapPages'] = $sitemapPages;

        $this->saveToXml(self::VIEW_STANDARD, $bindings, self::SITEMAP_TOP_RATED);
    }

    public function generateReviewStats(): void
    {
        $bindings = $this->getBindings();
        $timestamp = $this->getTimestampNow();

        $sitemapPages = [];

        $allowedDates = new AllowedDates();

        $yearListS1 = $allowedDates->releaseYearsByConsole(Console::ID_SWITCH_1);

        foreach ($yearListS1 as $year) {
            $sitemapPages[] = ['url' => route('reviews.landing.byYear', ['year' => $year]), 'lastmod' => $timestamp, 'changefreq' => 'weekly', 'priority' => '0.8'];
        }

        $bindings['SitemapPages'] = $sitemapPages;

        $this->saveToXml(self::VIEW_STANDARD, $bindings, self::SITEMAP_REVIEW_STATS);
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

        $sitemapPages[] = ['url' => route('games.browse.byDate.landing'), 'lastmod' => $timestamp, 'changefreq' => 'weekly', 'priority' => '0.8'];

        $allowedDates = new AllowedDates();

        $dateList = $allowedDates->allowedDates();

        foreach ($dateList as $dateListItem) {
            $sitemapPages[] = ['url' => route('games.browse.byDate.page', ['date' => $dateListItem]), 'lastmod' => $timestamp, 'changefreq' => 'weekly', 'priority' => '0.8'];
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

        $sitemapPages[] = ['url' => route('games.browse.byCategory.landing'), 'lastmod' => $timestamp, 'changefreq' => 'weekly', 'priority' => '0.8'];

        $categoryList = $repoCategory->getAll();

        foreach ($categoryList as $item) {
            $sitemapPages[] = ['url' => route('games.browse.byCategory.page', ['category' => $item->link_title]), 'lastmod' => $timestamp, 'changefreq' => 'weekly', 'priority' => '0.8'];
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

        $sitemapPages[] = ['url' => route('games.browse.byCollection.landing'), 'lastmod' => $timestamp, 'changefreq' => 'weekly', 'priority' => '0.8'];

        $collectionList = $repoCollection->getAll();

        foreach ($collectionList as $item) {
            $sitemapPages[] = ['url' => route('games.browse.byCollection.page', ['collection' => $item->link_title]), 'lastmod' => $timestamp, 'changefreq' => 'weekly', 'priority' => '0.8'];
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

        $sitemapPages[] = ['url' => route('games.browse.bySeries.landing'), 'lastmod' => $timestamp, 'changefreq' => 'weekly', 'priority' => '0.8'];

        $seriesList = $repoSeries->getAll();

        foreach ($seriesList as $item) {
            $sitemapPages[] = ['url' => route('games.browse.bySeries.page', ['series' => $item->link_title]), 'lastmod' => $timestamp, 'changefreq' => 'weekly', 'priority' => '0.8'];
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

        $sitemapPages[] = ['url' => route('games.browse.byTag.landing'), 'lastmod' => $timestamp, 'changefreq' => 'weekly', 'priority' => '0.8'];

        $tagList = $repoTag->getAll();

        foreach ($tagList as $item) {
            $sitemapPages[] = ['url' => route('games.browse.byTag.page', ['tag' => $item->link_title]), 'lastmod' => $timestamp, 'changefreq' => 'weekly', 'priority' => '0.8'];
        }

        $bindings['SitemapPages'] = $sitemapPages;

        $this->saveToXml(self::VIEW_STANDARD, $bindings, self::SITEMAP_TAG);
    }
}