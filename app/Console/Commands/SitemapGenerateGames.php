<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\Traits\SwitchServices;

class SitemapGenerateGames extends Command
{
    use SwitchServices;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'SitemapGenerateGames';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '(Re)generates the Games sitemap.';

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

        $bindings['GameList'] = $this->getServiceGame()->getGamesForSitemap();

        $xmlOutput = response()->view('sitemap.games', $bindings)->content();
        file_put_contents(storage_path().'/app/public/sitemaps/sitemap-games.xml', $xmlOutput);
    }
}
