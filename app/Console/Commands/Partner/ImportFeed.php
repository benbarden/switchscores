<?php

namespace App\Console\Commands\Partner;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\Domain\ReviewDraft\ImportByFeed;
use App\Domain\PartnerFeedLink\Repository as PartnerFeedLinkRepository;

class ImportFeed extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'PartnerImportFeed {feedId} {runMode?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports content from a partner feed.';

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
        $argFeedId = $this->argument('feedId');
        $argRunMode = $this->argument('runMode');

        $logger = Log::channel('cron');

        $logger->info(' *************** '.$this->signature.' *************** ');

        $repoPartnerFeedLink = new PartnerFeedLinkRepository;

        $partnerFeedLink = $repoPartnerFeedLink->find($argFeedId);
        if (!$partnerFeedLink) {
            $logger->error('Cannot find feed with id: '.$argFeedId);
            return 0;
        }

        $this->importByFeed->setLogger($logger);
        $this->importByFeed->setPartnerDetails($partnerFeedLink);
        $this->importByFeed->runImport();
    }
}
