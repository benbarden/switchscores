<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\Models\ReviewFeedItem;
use App\Models\Partner;
use App\Domain\ReviewSite\Repository as ReviewSiteRepository;

use App\Services\Feed\Parser;
use App\Services\Feed\TitleParser;
use App\Services\Game\TitleMatch as ServiceTitleMatch;

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
        $serviceReviewFeedItem = $this->getServiceReviewFeedItem();

        $repoReviewSite = new ReviewSiteRepository();

        $serviceTitleMatch = new ServiceTitleMatch();

        $feedItems = $serviceReviewFeedItem->getItemsToParse();

        if (!$feedItems) {
            $logger->info('No items to parse. Aborting.');
            return 0;
        }

        // Set up parser
        $titleParser = new TitleParser();
        $parser = new Parser($titleParser);

        foreach ($feedItems as $feedItem) {

            $siteId = $feedItem->site_id;
            $itemUrl = $feedItem->item_url;
            $itemTitle = $feedItem->item_title;

            $reviewSite = $repoReviewSite->find($siteId);
            if (!$reviewSite) {
                $logger->error('Cannot find review site! ['.$siteId.']');
                continue;
            }

            if ($reviewSite->review_import_method == Partner::REVIEW_IMPORT_BY_SCRAPER) {
                //$logger->info('Ignoring scraper items');
                continue;
            }

            if ($feedItem->feedImport) {
                $feedId = $feedItem->feedImport->feed_id;
                $partnerFeed = $this->getServicePartnerFeedLink()->find($feedId);
            } else {
                $partnerFeed = $this->getServicePartnerFeedLink()->getBySite($siteId);
            }
            if (!$partnerFeed) {
                $logger->error('Cannot find partner feed: '.$feedId);
                continue;
            }

            $siteName = $reviewSite->name;
            $titleMatchRulePattern = $partnerFeed->title_match_rule_pattern;
            $titleMatchRuleIndex = $partnerFeed->title_match_rule_index;

            try {

                $logger->info('*************************************************');
                $logger->info("Site: $siteName ($siteId)");
                $logger->info('Processing item: '.$itemTitle);

                if ($titleMatchRulePattern && ($titleMatchRuleIndex != null)) {

                    // New method
                    $serviceTitleMatch->setMatchRule($titleMatchRulePattern);
                    $serviceTitleMatch->prepareMatchRule();
                    $serviceTitleMatch->setMatchIndex($titleMatchRuleIndex);
                    $parsedTitle = $serviceTitleMatch->generate($itemTitle);
                    $logger->info('Using new parser');

                } else {

                    // Old method
                    $parser->setSiteId($siteId);
                    $parser->getTitleParser()->setTitle($itemTitle);
                    $parser->parseBySiteRules();
                    $parsedTitle = $parser->getTitleParser()->getTitle();
                    $logger->warning('Using old parser');

                }

                $feedItem->parsed_title = $parsedTitle;
                $logger->info("Parsed title: $parsedTitle");

                // Check for curly quotes
                $titleMatches = [];
                $titleMatches[] = $parsedTitle;
                if (strpos($parsedTitle, "’") !== false) {
                    $titleMatches[] = str_replace("’", "'", $parsedTitle);
                }

                $logger->info('Checking for matches: '.var_export($titleMatches, true));

                // Can we find a game from this title?
                $gameTitleHash = $serviceGameTitleHash->getByTitleGroup($titleMatches);
                if ($gameTitleHash) {
                    $feedItem->game_id = $gameTitleHash->game_id;
                    $parseStatus = ReviewFeedItem::PARSE_STATUS_AUTO_MATCHED;
                    $logger->info($parseStatus);
                    // Only mark as parsed if it matched - otherwise, we can check again tomorrow
                    $feedItem->parsed = 1;
                } else {
                    $parseStatus = ReviewFeedItem::PARSE_STATUS_COULD_NOT_LOCATE;
                    $logger->warning($parseStatus);
                }

                $feedItem->parse_status = $parseStatus;

                $feedItem->save();

            } catch (\Exception $e) {
                $logger->error('Got error: '.$e->getMessage().'; skipping');
            }

        }
    }
}
