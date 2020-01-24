<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;

class SitemapController extends Controller
{
    use SwitchServices;

    public function getTimestampNow()
    {
        $now = new \DateTime('now');
        $timestamp = $now->format('c');
        return $timestamp;
    }

    public function show()
    {
        $bindings = [];
        $timestamp = $this->getTimestampNow();
        $bindings['TimestampNow'] = $timestamp;

        return response()->view('sitemap.index', $bindings)->header('Content-Type', 'text/xml');
    }

    public function site()
    {
        $bindings = [];
        $timestamp = $this->getTimestampNow();
        $bindings['TimestampNow'] = $timestamp;

        $sitemapPages = [];
        $sitemapPages[] = array('url' => route('welcome'), 'lastmod' => $timestamp, 'changefreq' => 'daily', 'priority' => '1.0');
        $sitemapPages[] = array('url' => route('games.landing'), 'lastmod' => $timestamp, 'changefreq' => 'daily', 'priority' => '0.9');
        $sitemapPages[] = array('url' => route('games.browse.byTitle.landing'), 'lastmod' => $timestamp, 'changefreq' => 'daily', 'priority' => '0.9');
        $sitemapPages[] = array('url' => route('games.browse.byDate.landing'), 'lastmod' => $timestamp, 'changefreq' => 'daily', 'priority' => '0.9');
        $sitemapPages[] = array('url' => route('games.browse.byPrimaryType.landing'), 'lastmod' => $timestamp, 'changefreq' => 'daily', 'priority' => '0.9');
        $sitemapPages[] = array('url' => route('games.browse.bySeries.landing'), 'lastmod' => $timestamp, 'changefreq' => 'daily', 'priority' => '0.9');
        $sitemapPages[] = array('url' => route('games.browse.byTag.landing'), 'lastmod' => $timestamp, 'changefreq' => 'daily', 'priority' => '0.9');
        $sitemapPages[] = array('url' => route('games.onSale'), 'lastmod' => $timestamp, 'changefreq' => 'daily', 'priority' => '0.9');
        $sitemapPages[] = array('url' => route('games.recentReleases'), 'lastmod' => $timestamp, 'changefreq' => 'weekly', 'priority' => '0.8');
        $sitemapPages[] = array('url' => route('games.upcomingReleases'), 'lastmod' => $timestamp, 'changefreq' => 'weekly', 'priority' => '0.8');
        $sitemapPages[] = array('url' => route('news.landing'), 'lastmod' => $timestamp, 'changefreq' => 'weekly', 'priority' => '0.8');

        $bindings['SitemapPages'] = $sitemapPages;

        return response()->view('sitemap.standard', $bindings)->header('Content-Type', 'text/xml');
    }

    public function games()
    {
        $serviceGame = $this->getServiceGame();

        $xmlFilePath = storage_path().'/app/public/sitemaps/sitemap-games.xml';

        if (file_exists($xmlFilePath)) {

            return response()->file($xmlFilePath, ['Content-Type', 'text/xml']);

        } else {

            $bindings = [];
            $timestamp = $this->getTimestampNow();
            $bindings['TimestampNow'] = $timestamp;

            $bindings['GameList'] = $serviceGame->getAll();

            return response()->view('sitemap.games', $bindings)->header('Content-Type', 'text/xml');

        }
    }

    public function calendar()
    {
        $serviceCalendar = $this->getServiceGameCalendar();

        $xmlFilePath = storage_path().'/app/public/sitemaps/sitemap-calendar.xml';

        if (file_exists($xmlFilePath)) {

            return response()->file($xmlFilePath, ['Content-Type', 'text/xml']);

        } else {

            $bindings = [];
            $timestamp = $this->getTimestampNow();
            $bindings['TimestampNow'] = $timestamp;

            $sitemapPages = [];

            $sitemapPages[] = array(
                'url' => route('games.browse.byDate.landing'),
                'lastmod' => $timestamp,
                'changefreq' => 'weekly',
                'priority' => '0.8'
            );

            $dateList = $serviceCalendar->getAllowedDates();

            foreach ($dateList as $dateListItem) {

                $sitemapPages[] = array(
                    'url' => route('games.browse.byDate.page', ['date' => $dateListItem]),
                    'lastmod' => $timestamp,
                    'changefreq' => 'weekly',
                    'priority' => '0.8'
                );

            }

            $bindings['SitemapPages'] = $sitemapPages;

            return response()->view('sitemap.standard', $bindings)->header('Content-Type', 'text/xml');

        }
    }

    public function topRated()
    {
        $xmlFilePath = storage_path().'/app/public/sitemaps/sitemap-top-rated.xml';

        if (file_exists($xmlFilePath)) {

            return response()->file($xmlFilePath, ['Content-Type', 'text/xml']);

        } else {

            $bindings = [];

            $now = new \DateTime('now');
            $timestamp = $now->format('c');
            $bindings['TimestampNow'] = $timestamp;

            $sitemapPages = [];

            $sitemapPages[] = array(
                'url' => route('topRated.landing'),
                'lastmod' => $timestamp,
                'changefreq' => 'weekly',
                'priority' => '0.8'
            );

            $sitemapPages[] = array(
                'url' => route('topRated.allTime'),
                'lastmod' => $timestamp,
                'changefreq' => 'weekly',
                'priority' => '0.8'
            );

            $sitemapPages[] = array(
                'url' => route('topRated.byYear', ['year' => '2017']),
                'lastmod' => $timestamp,
                'changefreq' => 'weekly',
                'priority' => '0.8'
            );

            $sitemapPages[] = array(
                'url' => route('topRated.byYear', ['year' => '2018']),
                'lastmod' => $timestamp,
                'changefreq' => 'weekly',
                'priority' => '0.8'
            );

            $sitemapPages[] = array(
                'url' => route('topRated.byYear', ['year' => '2019']),
                'lastmod' => $timestamp,
                'changefreq' => 'weekly',
                'priority' => '0.8'
            );

            $bindings['SitemapPages'] = $sitemapPages;

            return response()->view('sitemap.standard', $bindings)->header('Content-Type', 'text/xml');

        }

    }

    public function reviews()
    {
        $servicePartner = $this->getServicePartner();

        $bindings = [];
        $timestamp = $this->getTimestampNow();
        $bindings['TimestampNow'] = $timestamp;

        $sitemapPages = array();
        $sitemapPages[] = array(
            'url' => route('reviews.landing'),
            'lastmod' => $timestamp,
            'changefreq' => 'weekly',
            'priority' => '0.8'
        );

        $reviewSiteList = $servicePartner->getActiveReviewSites();
        foreach ($reviewSiteList as $reviewSite) {
            $sitemapPages[] = array(
                'url' => route('reviews.site', ['linkTitle' => $reviewSite->link_title]),
                'lastmod' => $timestamp,
                'changefreq' => 'weekly',
                'priority' => '0.8'
            );
        }

        $bindings['SitemapPages'] = $sitemapPages;

        return response()->view('sitemap.standard', $bindings)->header('Content-Type', 'text/xml');
    }

    public function tags()
    {
        $serviceTag = $this->getServiceTag();

        $bindings = [];
        $timestamp = $this->getTimestampNow();
        $bindings['TimestampNow'] = $timestamp;

        $sitemapPages = array();
        $sitemapPages[] = array(
            'url' => route('games.browse.byTag.landing'),
            'lastmod' => $timestamp,
            'changefreq' => 'weekly',
            'priority' => '0.8'
        );

        $tagList = $serviceTag->getAll();
        foreach ($tagList as $tag) {
            $sitemapPages[] = array(
                'url' => route('games.browse.byTag.page', ['tag' => $tag->link_title]),
                'lastmod' => $timestamp,
                'changefreq' => 'weekly',
                'priority' => '0.8'
            );
        }

        $bindings['SitemapPages'] = $sitemapPages;

        return response()->view('sitemap.standard', $bindings)->header('Content-Type', 'text/xml');
    }

    public function news()
    {
        $serviceNews = $this->getServiceNews();

        $xmlFilePath = storage_path().'/app/public/sitemaps/sitemap-news.xml';

        if (file_exists($xmlFilePath)) {

            return response()->file($xmlFilePath, ['Content-Type', 'text/xml']);

        } else {

            $bindings = [];
            $timestamp = $this->getTimestampNow();
            $bindings['TimestampNow'] = $timestamp;

            $bindings['NewsList'] = $newsList = $serviceNews->getAll();

            return response()->view('sitemap.news', $bindings)->header('Content-Type', 'text/xml');

        }
    }
}
