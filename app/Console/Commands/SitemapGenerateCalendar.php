<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\Domain\GameCalendar\AllowedDates as GameCalendarAllowedDates;

class SitemapGenerateCalendar extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'SitemapGenerateCalendar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '(Re)generates the Calendar sitemap.';

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
     * @throws \Exception
     * @return mixed
     */
    public function handle()
    {
        $logger = Log::channel('cron');

        $logger->info(' *************** '.$this->signature.' *************** ');

        $allowedDates = new GameCalendarAllowedDates();

        $bindings = [];

        $now = new \DateTime('now');
        $timestamp = $now->format('c');
        $bindings['TimestampNow'] = $timestamp;

        $sitemapPages = [];

        $sitemapPages[] = array(
            'url' => route('games.browse.byDate.landing'),
            'lastmod' => $timestamp,
            'changefreq' => 'weekly',
            'priority' => '0.8'
        );

        $dateList = $allowedDates->allowedDates(false);

        foreach ($dateList as $dateListItem) {

            $sitemapPages[] = array(
                'url' => route('games.browse.byDate.page', ['date' => $dateListItem]),
                'lastmod' => $timestamp,
                'changefreq' => 'weekly',
                'priority' => '0.8'
            );

        }

        $bindings['SitemapPages'] = $sitemapPages;

        $xmlOutput = response()->view('sitemap.standard', $bindings)->content();
        file_put_contents(storage_path().'/app/public/sitemaps/sitemap-calendar.xml', $xmlOutput);
    }
}
