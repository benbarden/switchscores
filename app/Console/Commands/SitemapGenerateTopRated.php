<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Services\GameCalendarService;

class SitemapGenerateTopRated extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'SitemapGenerateTopRated';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '(Re)generates the Top Rated sitemap.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $serviceCalendar = resolve('Services\GameCalendarService');
        /* @var GameCalendarService $serviceCalendar */

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
            'url' => route('topRated.byMonthLanding'),
            'lastmod' => $timestamp,
            'changefreq' => 'weekly',
            'priority' => '0.8'
        );

        $dateList = $serviceCalendar->getAllowedDates();

        foreach ($dateList as $dateListItem) {

            $sitemapPages[] = array(
                'url' => route('topRated.byMonthPage', ['date' => $dateListItem]),
                'lastmod' => $timestamp,
                'changefreq' => 'weekly',
                'priority' => '0.8'
            );

        }

        $bindings['SitemapPages'] = $sitemapPages;

        $xmlOutput = response()->view('sitemap.standard', $bindings)->content();
        file_put_contents(storage_path().'/app/public/sitemaps/sitemap-top-rated.xml', $xmlOutput);
    }
}
