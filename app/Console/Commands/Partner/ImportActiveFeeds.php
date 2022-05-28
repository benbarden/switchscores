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

        $serviceImporter = new ImportReviewFeed($logger);

        foreach ($partnerFeedLinks as $partnerFeedLink) {
            $serviceImporter->setPartnerFeedLink($partnerFeedLink);
            $serviceImporter->runImport();
        }
    }
}
