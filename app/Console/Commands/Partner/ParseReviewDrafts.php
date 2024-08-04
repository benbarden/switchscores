<?php

namespace App\Console\Commands\Partner;

use App\Domain\ReviewDraft\Repository as ReviewDraftRepository;
use App\Domain\ReviewDraft\ParseTitle;
use App\Domain\ReviewDraft\ParseScore;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ParseReviewDrafts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'PartnerParseReviewDrafts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parses titles of review drafts, and attempts to match them to games. Also parses scores where possible.';

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
        $logger = Log::channel('cron');

        $logger->info(' *************** '.$this->signature.' *************** ');

        $parseTitle = new ParseTitle($logger);
        $parseScore = new ParseScore($logger);

        $repoReviewDraft = new ReviewDraftRepository();

        $reviewDrafts = $repoReviewDraft->getUnprocessed();

        if (!$reviewDrafts) {
            $logger->info('No items to parse. Aborting.');
            return 0;
        }

        foreach ($reviewDrafts as $reviewDraft) {

            $parseTitle->parse($reviewDraft);
            $parseScore->parse($reviewDraft);

        }
    }
}
