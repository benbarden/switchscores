<?php

namespace App\Console\Commands\Partner;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\Domain\ReviewDraft\ImportByFeed;
use App\Domain\PartnerFeedLink\Repository as PartnerFeedLinkRepository;

class ImportActiveFeeds extends Command
{
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
    public function __construct(
        private ImportByFeed $importByFeed
    )
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

        $repoPartnerFeedLink = new PartnerFeedLinkRepository;

        $partnerFeedLinks = $repoPartnerFeedLink->getActive();
        if (!$partnerFeedLinks) {
            $logger->error('No feeds to import!');
            return 0;
        }

        foreach ($partnerFeedLinks as $partnerFeedLink) {
            $this->importByFeed->setLogger($logger);
            try {
                $this->importByFeed->setPartnerDetails($partnerFeedLink);
                $this->importByFeed->runImport();
            } catch (\Exception $e) {
                $logger->error($e->getMessage());
            }
        }
    }
}
