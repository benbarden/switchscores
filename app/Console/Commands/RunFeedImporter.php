<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\Partner;
use App\Services\PartnerService;
use App\Services\FeedItemReviewService;
use App\Services\Feed\Importer;

use App\FeedItemReview;
use Carbon\Carbon;

use App\Services\Game\TitleMatch as ServiceTitleMatch;

class RunFeedImporter extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'RunFeedImporter';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs the feed importer.';

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

        $partnerService = resolve('Services\PartnerService');
        /* @var PartnerService $partnerService */
        $feedItemReviewService = resolve('Services\FeedItemReviewService');
        /* @var FeedItemReviewService $feedItemReviewService */
        $reviewSites = $partnerService->getReviewSiteFeedUrls();

        $gameService = resolve('Services\GameService');
        /* @var GameService $gameService */

        if (!$reviewSites) {
            $logger->info('No sites found with feed URLs. Aborting.');
            return true;
        }

        foreach ($reviewSites as $reviewSite) {

            $siteId = $reviewSite->id;
            $siteName = $reviewSite->name;
            $feedUrl = $reviewSite->feed_url;
            if (!$feedUrl) continue;

            $logger->info(sprintf('Site: %s - Feed URL: %s', $siteName, $feedUrl));

            try {

                // Set up the importer for this site
                $feedImporter = new Importer();
                $feedImporter->loadRemoteFeedData($feedUrl);
                $feedImporter->setSiteId($siteId);
                $feedData = $feedImporter->getFeedData();

                if (array_key_exists('channel', $feedData)) {

                    foreach ($feedData['channel']['item'] as $feedItem) {

                        // RSS

                        // Generate the model
                        $feedItemReview = $feedImporter->generateModel($feedItem);
                        $itemTitle = $feedItemReview->item_title;
                        $itemUrl = $feedItemReview->item_url;
                        $itemDate = $feedItemReview->item_date;

                        // Check if it's already been imported
                        $dbExistingItem = $feedItemReviewService->getByItemUrl($itemUrl);
                        if ($dbExistingItem) {
                            //$logger->warn('Already imported: '.$itemUrl);
                            continue;
                        }

                        // Silently bypass historic reviews - removes some log noise
                        if ($feedItemReview->isHistoric() && !$reviewSite->allowHistoric()) {
                            //$logger->warn('Skipping historic review: '.$itemUrl.' - Date: '.$itemDate);
                            continue;
                        }

                        // Check if a feed URL prefix is set, and if so, compare it
                        $feedUrlPrefix = $reviewSite->feed_url_prefix;
                        if ($feedUrlPrefix) {
                            $fullPrefix = $reviewSite->website_url.$feedUrlPrefix;
                            if (substr($itemUrl, 0, strlen($fullPrefix)) != $fullPrefix) {
                                $logger->warn('Does not match feed URL prefix: '.$itemUrl.' - Date: '.$itemDate);
                                continue;
                            } else {
                                //$logger->warn('URL prefix matched!: '.$itemUrl.' - Date: '.$itemDate);
                                //continue;
                            }
                        }

                        // Output some details
                        $logger->info('Importing item: '.$itemUrl);

                        // All good - add it as a feed item
                        $feedItemReview->load_status = 'Loaded OK';
                        $feedItemReview->save();

                    }

                } elseif (array_key_exists('entry', $feedData)) {

                    // Atom

                    foreach ($feedData['entry'] as $feedItem) {

                        $feedItemReview = new FeedItemReview();

                        // Basic fields
                        $feedItemReview->site_id = $siteId;
                        $itemTitle = $feedItem['title'];
                        $feedItemReview->item_title = $itemTitle;

                        // URL
                        $itemUrl = null;
                        $feedItemLinks = $feedItem['link'];
                        foreach ($feedItemLinks as $itemLinkTemp) {
                            $itemLinkTempData = $itemLinkTemp['@attributes'];
                            if ($itemLinkTempData['rel'] == 'alternate') {
                                $itemUrl = $itemLinkTempData['href'];
                                break;
                            }
                        }
                        if ($itemUrl != null) {
                            $feedItemReview->item_url = $itemUrl;
                        }

                        // Date
                        $itemDateModel = new Carbon($feedItem['published']);
                        $itemDate = $itemDateModel->format('Y-m-d H:i:s');
                        $feedItemReview->item_date = $itemDate;

                        // Check if it's already been imported
                        $dbExistingItem = $feedItemReviewService->getByItemUrl($itemUrl);
                        if ($dbExistingItem) {
                            //$logger->warn('Already imported: '.$itemUrl);
                            continue;
                        }

                        // Special rules for Digitally Downloaded
                        if ($reviewSite->name == 'Digitally Downloaded') {

                            $serviceTitleMatch = new ServiceTitleMatch();

                            $titleMatchRulePattern = $reviewSite->title_match_rule_pattern;
                            $titleMatchIndex = $reviewSite->title_match_index;
                            if ($titleMatchRulePattern && ($titleMatchIndex != null)) {

                                // New method
                                $serviceTitleMatch->setMatchRule($titleMatchRulePattern);
                                $serviceTitleMatch->prepareMatchRule();
                                $serviceTitleMatch->setMatchIndex($titleMatchIndex);
                                $parsedTitle = $serviceTitleMatch->generate($itemTitle);

                                // Can we find a game from this title?
                                // NB. If there's no match, we'll skip this item altogether
                                //$logger->info('Title: '.$itemTitle);
                                //$logger->info('Parsed title: '.$parsedTitle);
                                if (!$parsedTitle) {
                                    $parseStatus = 'Does not match title rule; skipping';
                                    $logger->warn($parseStatus);
                                    continue;
                                }

                            }
                        }

                        // Check if a feed URL prefix is set, and if so, compare it
                        $feedUrlPrefix = $reviewSite->feed_url_prefix;
                        if ($feedUrlPrefix) {
                            $fullPrefix = $reviewSite->url.$feedUrlPrefix;
                            if (substr($itemUrl, 0, strlen($fullPrefix)) != $fullPrefix) {
                                $logger->warn('Does not match feed URL prefix: '.$itemUrl.' - Date: '.$itemDate);
                                continue;
                            } else {
                                //$logger->warn('URL prefix matched!: '.$itemUrl.' - Date: '.$itemDate);
                                //continue;
                            }
                        }

                        // Check that it's not a historic review
                        if ($feedItemReview->isHistoric() && !$reviewSite->allowHistoric()) {
                            $logger->warn('Skipping historic review: '.$itemUrl.' - Date: '.$itemDate);
                            continue;
                        }

                        // Output some details
                        $logger->info('Importing item: '.$itemUrl);

                        // All good - add it as a feed item
                        $feedItemReview->load_status = 'Loaded OK';
                        $feedItemReview->save();

                    }

                }

            } catch (\Exception $e) {
                $logger->error('Got error: '.$e->getMessage().'; skipping');
            }

        }

    }
}
