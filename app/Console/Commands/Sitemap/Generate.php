<?php

namespace App\Console\Commands\Sitemap;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\Domain\Sitemap\Generator;

class Generate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'SitemapGenerate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '(Re)generates the sitemaps.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        private Generator $sitemapGenerator
    )
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

        $logger->info('Generating: Sitemap index');
        $this->sitemapGenerator->generateIndex();
        $logger->info('Generating: Site pages');
        $this->sitemapGenerator->generateSite();
        $logger->info('Generating: Top Rated');
        $this->sitemapGenerator->generateTopRated();
        $logger->info('Generating: Review partners');
        $this->sitemapGenerator->generateReviewPartners();
        $logger->info('Generating: Browse by date, category, collection, series, tag');
        $this->sitemapGenerator->generateCalendar();
        $this->sitemapGenerator->generateCategory();
        $this->sitemapGenerator->generateCollection();
        $this->sitemapGenerator->generateSeries();
        $this->sitemapGenerator->generateTag();

        $logger->info('Generating: Games');
        $this->sitemapGenerator->generateGames();

        $logger->info('Complete');
    }
}
