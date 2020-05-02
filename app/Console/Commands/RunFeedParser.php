<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\Services\Feed\Parser;
use App\Services\Feed\TitleParser;

use App\Services\Game\TitleMatch as ServiceTitleMatch;

use App\ReviewFeedItem;

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
        $servicePartner = $this->getServicePartner();

        $serviceTitleMatch = new ServiceTitleMatch();

        $feedItems = $serviceReviewFeedItem->getItemsToParse();

        if (!$feedItems) {
            $logger->info('No items to parse. Aborting.');
            return true;
        }

        // Set up parser
        $titleParser = new TitleParser();
        $parser = new Parser($titleParser);

        foreach ($feedItems as $feedItem) {

            $siteId = $feedItem->site_id;
            $itemUrl = $feedItem->item_url;
            $itemTitle = $feedItem->item_title;

            $reviewSite = $servicePartner->find($siteId);
            if (!$reviewSite) {
                $logger->error('Cannot find review site! ['.$siteId.']');
                continue;
            }

            $siteName = $reviewSite->name;
            $titleMatchRulePattern = $reviewSite->title_match_rule_pattern;
            $titleMatchIndex = $reviewSite->title_match_index;

            try {

                $logger->info('*************************************************');
                $logger->info("Site: $siteName ($siteId)");
                $logger->info('Processing item: '.$itemTitle);

                if ($titleMatchRulePattern && ($titleMatchIndex != null)) {

                    // New method
                    $serviceTitleMatch->setMatchRule($titleMatchRulePattern);
                    $serviceTitleMatch->prepareMatchRule();
                    $serviceTitleMatch->setMatchIndex($titleMatchIndex);
                    $parsedTitle = $serviceTitleMatch->generate($itemTitle);
                    $logger->info('Using new parser');

                } else {

                    // Old method
                    $parser->setSiteId($siteId);
                    $parser->getTitleParser()->setTitle($itemTitle);
                    $parser->parseBySiteRules();
                    $parsedTitle = $parser->getTitleParser()->getTitle();
                    $logger->warn('Using old parser');

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
                    $logger->warn($parseStatus);
                }

                $feedItem->parse_status = $parseStatus;

                $feedItem->save();

            } catch (\Exception $e) {
                $logger->error('Got error: '.$e->getMessage().'; skipping');
            }

        }
    }
}
