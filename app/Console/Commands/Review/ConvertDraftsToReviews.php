<?php

namespace App\Console\Commands\Review;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\Domain\ReviewDraft\Repository as ReviewDraftRepository;
use App\Domain\ReviewDraft\ConvertToReviewLink;

class ConvertDraftsToReviews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ReviewConvertDraftsToReviews {siteId?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates reviews from review drafts.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        private ConvertToReviewLink $convertToReviewLink
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
        $argSiteId = $this->argument('siteId');

        $logger = Log::channel('cron');
        $this->convertToReviewLink->setLogger($logger);

        $logger->info(' *************** '.$this->signature.' *************** ');

        $repoReviewDraft = new ReviewDraftRepository();

        if ($argSiteId) {
            $draftsForProcessing = $repoReviewDraft->getReadyForProcessingBySite($argSiteId);
            $logger->info('Processing for site: '.$argSiteId);
        } else {
            $draftsForProcessing = $repoReviewDraft->getReadyForProcessing();
            $logger->info('Processing for all sites');
        }

        if (!$draftsForProcessing) {
            $logger->info('No items to process');
            return 0;
        }

        $logger->info('Found '.$draftsForProcessing->count().' item(s)');

        foreach ($draftsForProcessing as $draftItem) {

            try {
                $this->convertToReviewLink->processItem($draftItem);
            } catch (\Exception $e) {
                $logger->error('Got error: '.$e->getMessage().'; skipping');
            }

        }
    }
}
