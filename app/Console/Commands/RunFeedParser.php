<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\Models\ReviewDraft;
use App\Models\Partner;
use App\Domain\ReviewSite\Repository as ReviewSiteRepository;
use App\Domain\ReviewDraft\Repository as ReviewDraftRepository;

use App\Domain\GameTitleMatch\MatchRule;

use App\Traits\SwitchServices;

class RunFeedParser extends Command
{
    use SwitchServices;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'RunFeedParser';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs the feed parser.';

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

        $serviceGameTitleHash = $this->getServiceGameTitleHash();

        $repoReviewSite = new ReviewSiteRepository();
        $repoReviewDraft = new ReviewDraftRepository();

        $reviewDrafts = $repoReviewDraft->getUnparsed();

        if (!$reviewDrafts) {
            $logger->info('No items to parse. Aborting.');
            return 0;
        }

        foreach ($reviewDrafts as $reviewDraft) {

            $siteId = $reviewDraft->site_id;
            $itemUrl = $reviewDraft->item_url;
            $itemTitle = $reviewDraft->item_title;

            $reviewSite = $repoReviewSite->find($siteId);
            if (!$reviewSite) {
                $logger->error('Cannot find review site! ['.$siteId.']');
                continue;
            }

            if ($reviewSite->review_import_method == Partner::REVIEW_IMPORT_BY_SCRAPER) {
                //$logger->info('Ignoring scraper items');
                continue;
            }

            $siteName = $reviewSite->name;

            $partnerFeed = $this->getServicePartnerFeedLink()->getBySite($siteId);
            if (!$partnerFeed) {
                $logger->error('Cannot find partner feed for site: '.$siteName);
                continue;
            }

            $matchRulePattern = $partnerFeed->title_match_rule_pattern;
            $matchRuleIndex = $partnerFeed->title_match_rule_index;

            try {

                $logger->info('*************************************************');
                $logger->info("Site: $siteName ($siteId)");
                $logger->info('Processing item: '.$itemTitle);

                if (!$matchRulePattern) {
                    $logger->error('No match rule pattern for site: '.$siteName);
                    continue;
                } elseif (is_null($matchRuleIndex)) {
                    $logger->error('No match rule index for site: '.$siteName);
                    continue;
                }

                $matchRule = new MatchRule($matchRulePattern, $matchRuleIndex);
                $titleMatches = $matchRule->generateMatch($itemTitle);

                $parsedTitle = $matchRule->getParsedTitle();

                $reviewDraft->parsed_title = $parsedTitle;
                $logger->info("Parsed title: $parsedTitle");

                if ($titleMatches == null) {
                    $logger->warning('No matches found; continuing');
                    continue;
                }

                $logger->info('Checking for matches: '.var_export($titleMatches, true));

                // Can we find a game from this title?
                $gameTitleHash = $serviceGameTitleHash->getByTitleGroup($titleMatches);
                if ($gameTitleHash) {
                    $reviewDraft->game_id = $gameTitleHash->game_id;
                    $parseStatus = ReviewDraft::PARSE_STATUS_AUTO_MATCHED;
                    $logger->info($parseStatus);
                    $reviewDraft->parse_status = $parseStatus;
                } else {
                    $parseStatus = ReviewDraft::PARSE_STATUS_COULD_NOT_LOCATE;
                    $logger->warning($parseStatus);
                }

                $reviewDraft->save();

            } catch (\Exception $e) {
                $logger->error('Got error: '.$e->getMessage().'; skipping');
            }

        }
    }
}
