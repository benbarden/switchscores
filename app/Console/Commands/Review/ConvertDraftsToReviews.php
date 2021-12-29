<?php

namespace App\Console\Commands\Review;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\Domain\ReviewDraft\Repository as ReviewDraftRepository;
use App\Domain\ReviewLink\Repository as ReviewLinkRepository;
use App\Domain\ReviewLink\Builder as ReviewLinkBuilder;
use App\Domain\ReviewLink\Director as ReviewLinkDirector;
use App\Domain\ReviewLink\Calculations as ReviewLinkCalculations;
use App\Domain\ReviewLink\Stats as ReviewLinkStats;
use App\Domain\Partner\Repository as PartnerRepository;
use App\Domain\Game\Repository as GameRepository;
use App\Domain\QuickReview\Repository as QuickReviewRepository;
use App\Domain\ReviewDraft\ConvertToReviewLink;

class ConvertDraftsToReviews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ReviewConvertDraftsToReviews';

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

        $repoReviewDraft = new ReviewDraftRepository();
        $convertToReviewLink = new ConvertToReviewLink($logger);

        $draftsForProcessing = $repoReviewDraft->getReadyForProcessing();

        if (!$draftsForProcessing) {
            $logger->info('No items to process');
            return true;
        }

        $logger->info('Found '.$draftsForProcessing->count().' item(s)');

        foreach ($draftsForProcessing as $draftItem) {

            try {
                $convertToReviewLink->processItem($draftItem);
            } catch (\Exception $e) {
                $logger->error('Got error: '.$e->getMessage().'; skipping');
            }

        }

        return true;
    }
}
