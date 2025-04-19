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

        $generator = new Generator();
        $logger->info('Generating: Sitemap index');
        $generator->generateIndex();
        $logger->info('Generating: Site pages');
        $generator->generateSite();
        $logger->info('Generating: Top Rated');
        $generator->generateTopRated();
        $logger->info('Generating: Review stats, Review partners');
        $generator->generateReviewStats();
        $generator->generateReviewPartners();
        $logger->info('Generating: Browse by date, category, collection, series, tag');
        $generator->generateCalendar();
        $generator->generateCategory();
        $generator->generateCollection();
        $generator->generateSeries();
        $generator->generateTag();

        $logger->info('Generating: Games');
        $generator->generateGames();

        $logger->info('Complete');
    }
}
