<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\Services\NewsService;

class SitemapGenerateNews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'SitemapGenerateNews';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '(Re)generates the News sitemap.';

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

        $bindings = [];

        $now = new \DateTime('now');
        $timestamp = $now->format('c');
        $bindings['TimestampNow'] = $timestamp;

        $newsService = resolve('Services\NewsService');
        /* @var NewsService $newsService */

        $bindings['NewsList'] = $newsService->getAll();

        $xmlOutput = response()->view('sitemap.news', $bindings)->content();
        file_put_contents(storage_path().'/app/public/sitemaps/sitemap-news.xml', $xmlOutput);
    }
}
