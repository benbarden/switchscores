<?php

namespace App\Console\Commands\ReviewSite;

use App\Domain\ReviewSite\ReviewSiteStats;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ReviewSiteUpdateStats {siteId?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates status, review count, and last review date for review sites.';

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
        $argSiteId = $this->argument('siteId');

        $logger = Log::channel('cron');

        $logger->info(' *************** '.$this->signature.' *************** ');

        $reviewSiteStats = new ReviewSiteStats($logger);
        if ($argSiteId) {
            $reviewSiteStats->updateSite($argSiteId);
        } else {
            $reviewSiteStats->updateAll();
        }
    }
}
