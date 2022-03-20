<?php

namespace App\Console\Commands\Partner;

use App\Services\PartnerFeedLink\ImportReviewFeed;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\Services\Feed\Importer;
use App\Services\UrlService;

use App\Exceptions\Review\AlreadyImported;
use App\Exceptions\Review\HistoricEntry;
use App\Exceptions\Review\FeedUrlPrefixNotMatched;
use App\Exceptions\Review\TitleRuleNotMatched;

use App\Traits\SwitchServices;

class ImportFeed extends Command
{
    use SwitchServices;

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
        $argFeedId = $this->argument('feedId');
        $argRunMode = $this->argument('runMode');

        $logger = Log::channel('cron');

        $logger->info(' *************** '.$this->signature.' *************** ');

        $partnerFeedLink = $this->getServicePartnerFeedLink()->find($argFeedId);
        if (!$partnerFeedLink) {
            $logger->error('Cannot find feed with id: '.$argFeedId);
            return 0;
        }

        $serviceImporter = new ImportReviewFeed($logger);
        $serviceImporter->setPartnerFeedLink($partnerFeedLink);
        if ($argRunMode == 'test') {
            $serviceImporter->setIsTest(true);
            $serviceImporter->clearPreviousTests();
        }
        $serviceImporter->createFeedImport();
        $serviceImporter->runImport();
    }
}
