<?php

namespace App\Console\Commands\Partner;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\Domain\ReviewDraft\ImportByFeed;

use App\Traits\SwitchServices;

class ImportActiveFeeds extends Command
{
    use SwitchServices;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'PartnerImportActiveFeeds';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports content from all active partner feeds.';

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
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle()
    {
        $logger = Log::channel('cron');

        $logger->info(' *************** '.$this->signature.' *************** ');

        $partnerFeedLinks = $this->getServicePartnerFeedLink()->getActive();
        if (!$partnerFeedLinks) {
            $logger->error('No feeds to import!');
            return 0;
        }

        foreach ($partnerFeedLinks as $partnerFeedLink) {
            $importer = new ImportByFeed($partnerFeedLink, $logger);
            $importer->runImport();
        }
    }
}
