<?php

namespace App\Http\Controllers;

use App\Services\GameService;
use App\Services\NewsService;

class SitemapController extends BaseController
{
    public function getTimestampNow()
    {
        $now = new \DateTime('now');
        $timestamp = $now->format('c');
        return $timestamp;
    }

    public function show()
    {
        $bindings = array();
        $timestamp = $this->getTimestampNow();
        $bindings['TimestampNow'] = $timestamp;

        return response()->view('sitemap.index', $bindings)->header('Content-Type', 'text/xml');
    }

    public function site()
    {
        $bindings = array();
        $timestamp = $this->getTimestampNow();
        $bindings['TimestampNow'] = $timestamp;

        $sitemapPages = array();
        $sitemapPages[] = array('url' => route('welcome'), 'lastmod' => $timestamp, 'changefreq' => 'daily', 'priority' => '1.0');
        $sitemapPages[] = array('url' => route('news.landing'), 'lastmod' => $timestamp, 'changefreq' => 'daily', 'priority' => '0.9');
        $sitemapPages[] = array('url' => route('games.list.released'), 'lastmod' => $timestamp, 'changefreq' => 'weekly', 'priority' => '0.8');
        $sitemapPages[] = array('url' => route('games.list.upcoming'), 'lastmod' => $timestamp, 'changefreq' => 'weekly', 'priority' => '0.8');

        $sitemapPages[] = array('url' => route('reviews.landing'), 'lastmod' => $timestamp, 'changefreq' => 'daily', 'priority' => '0.8');
        $sitemapPages[] = array('url' => route('reviews.topRated.allTime'), 'lastmod' => $timestamp, 'changefreq' => 'weekly', 'priority' => '0.8');
        $sitemapPages[] = array('url' => route('reviews.gamesNeedingReviews'), 'lastmod' => $timestamp, 'changefreq' => 'weekly', 'priority' => '0.8');

        $bindings['SitemapPages'] = $sitemapPages;

        return response()->view('sitemap.standard', $bindings)->header('Content-Type', 'text/xml');
    }

    public function charts()
    {
        $bindings = array();
        $timestamp = $this->getTimestampNow();
        $bindings['TimestampNow'] = $timestamp;

        $sitemapPages[] = array('url' => route('charts.landing'), 'lastmod' => $timestamp, 'changefreq' => 'weekly', 'priority' => '0.8');

        $chartsDateService = resolve('Services\ChartsDateService');
        $chartDatesEu = $chartsDateService->getDateList('eu');
        $chartDatesUs = $chartsDateService->getDateList('us');
        foreach ($chartDatesEu as $chartDate) {
            $sitemapPages[] = array(
                'url' => route('charts.date.show', ['countryCode' => 'eu', 'date' => $chartDate->chart_date]),
                'lastmod' => $timestamp,
                'changefreq' => 'weekly',
                'priority' => '0.8'
            );
        }
        foreach ($chartDatesUs as $chartDate) {
            $sitemapPages[] = array(
                'url' => route('charts.date.show', ['countryCode' => 'us', 'date' => $chartDate->chart_date]),
                'lastmod' => $timestamp,
                'changefreq' => 'weekly',
                'priority' => '0.8'
            );
        }

        $sitemapPages[] = array('url' => route('charts.mostAppearances'), 'lastmod' => $timestamp, 'changefreq' => 'weekly', 'priority' => '0.8');
        $sitemapPages[] = array('url' => route('charts.gamesAtPositionLanding'), 'lastmod' => $timestamp, 'changefreq' => 'weekly', 'priority' => '0.8');
        for ($chartPos = 1; $chartPos <= 15; $chartPos++) {
            $sitemapPages[] = array('url' => route('charts.gamesAtPosition', ['position' => "$chartPos"]), 'lastmod' => $timestamp, 'changefreq' => 'weekly', 'priority' => '0.8');
        }

        $bindings['SitemapPages'] = $sitemapPages;

        return response()->view('sitemap.standard', $bindings)->header('Content-Type', 'text/xml');
    }

    public function genres()
    {
        $bindings = array();
        $timestamp = $this->getTimestampNow();
        $bindings['TimestampNow'] = $timestamp;

        $sitemapPages = array();
        $sitemapPages[] = array(
            'url' => route('games.genres.landing'),
            'lastmod' => $timestamp,
            'changefreq' => 'weekly',
            'priority' => '0.8'
        );

        $serviceGenre = resolve('Services\GenreService');
        $genreList = $serviceGenre->getAll();
        foreach ($genreList as $genre) {
            $sitemapPages[] = array(
                'url' => route('games.genres.item', ['linkTitle' => $genre->link_title]),
                'lastmod' => $timestamp,
                'changefreq' => 'weekly',
                'priority' => '0.8'
            );
        }

        $bindings['SitemapPages'] = $sitemapPages;

        return response()->view('sitemap.standard', $bindings)->header('Content-Type', 'text/xml');
    }

    public function games()
    {
        $bindings = array();
        $timestamp = $this->getTimestampNow();
        $bindings['TimestampNow'] = $timestamp;

        $gameService = resolve('Services\GameService');
        /* @var $gameService GameService */
        $gameList = $gameService->getAll();

        $bindings['GameList'] = $gameList;

        return response()->view('sitemap.games', $bindings)->header('Content-Type', 'text/xml');
    }

    public function news()
    {
        $bindings = array();
        $timestamp = $this->getTimestampNow();
        $bindings['TimestampNow'] = $timestamp;

        $newsService = resolve('Services\NewsService');
        /* @var $newsService NewsService */
        $newsList = $newsService->getAll();

        $bindings['NewsList'] = $newsList;

        return response()->view('sitemap.news', $bindings)->header('Content-Type', 'text/xml');
    }
}
