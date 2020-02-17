<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use Carbon\Carbon;

use App\ReviewFeedItem;
use App\Services\Feed\Importer;
use App\Services\UrlService;
use App\Services\Game\TitleMatch as ServiceTitleMatch;

use App\Exceptions\Review\AlreadyImported;
use App\Exceptions\Review\HistoricEntry;
use App\Exceptions\Review\FeedUrlPrefixNotMatched;

use App\Traits\SwitchServices;

class RunFeedImporter extends Command
{
    use SwitchServices;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'RunFeedImporter {siteId?}';

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
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle()
    {
        $argSiteId = $this->argument('siteId');

        $logger = Log::channel('cron');

        $logger->info(' *************** '.$this->signature.' *************** ');

        $servicePartner = $this->getServicePartner();
        $serviceReviewFeedItem = $this->getServiceReviewFeedItem();

        $reviewSites = $servicePartner->getReviewSiteFeedUrls();

        if (!$reviewSites) {
            $logger->info('No sites found with feed URLs. Aborting.');
            return true;
        }

        // Create import record
        if ($argSiteId) {
            $importSiteId = $argSiteId;
        } else {
            $importSiteId = null;
        }
        $reviewFeedImport = $this->getServiceReviewFeedImport()->createCron($importSiteId);
        $feedImportId = $reviewFeedImport->id;

        // Create feed items
        $serviceUrl = new UrlService();

        foreach ($reviewSites as $reviewSite) {

            $siteId = $reviewSite->id;
            $siteName = $reviewSite->name;
            $feedUrl = $reviewSite->feed_url;
            if (!$feedUrl) continue;

            if ($argSiteId && ($siteId != $argSiteId)) continue;

            $logger->info(sprintf('Site %s: %s - Feed URL: %s', $siteId, $siteName, $feedUrl));

            try {

                // Set up the importer for this site
                $feedImporter = new Importer();
                // Dirty hack for Wix
                if ($siteId == 30) {
                    $isWix = true;
                } else {
                    $isWix = false;
                }
                $feedImporter->loadRemoteFeedData($feedUrl, $isWix);
                $feedImporter->setSiteId($siteId);
                $feedData = $feedImporter->getFeedData();

                // Make feed item array
                $feedItemsToProcess = [];
                if ($isWix) {

                    foreach ($feedData->channel->item as $feedItem) {

                        $feedItemsToProcess[] = $feedItem;

                    }

                } elseif (array_key_exists('channel', $feedData)) {

                    foreach ($feedData['channel']['item'] as $feedItem) {

                        $feedItemsToProcess[] = $feedItem;

                    }

                } elseif (array_key_exists('item', $feedData)) {

                    // Video Chums - custom feed
                    foreach ($feedData['item'] as $feedItem) {

                        $feedItemsToProcess[] = $feedItem;

                    }

                }

                if (count($feedItemsToProcess) > 0) {

                    foreach ($feedItemsToProcess as $feedItem) {

                        try {

                            $reviewFeedItem = $feedImporter->processItemRss($isWix, $feedItem, $reviewSite, $serviceUrl, $serviceReviewFeedItem);
                            $logger->info('Importing item with date: '.$reviewFeedItem->item_date.'; URL: '.$reviewFeedItem->item_url);
                            $reviewFeedItem->import_id = $feedImportId;
                            $reviewFeedItem->save();

                        } catch (AlreadyImported $e) {

                            //$logger->error('Got error: '.$e->getMessage().'; skipping');

                        } catch (HistoricEntry $e) {

                            //$logger->error('Got error: '.$e->getMessage().'; skipping');

                        } catch (FeedUrlPrefixNotMatched $e) {

                            //$logger->error('Got error: '.$e->getMessage().'; skipping');

                        }

                    }

                } elseif (array_key_exists('entry', $feedData)) {

                    // Atom

                    foreach ($feedData['entry'] as $feedItem) {

                        $reviewFeedItem = new ReviewFeedItem();

                        // Basic fields
                        $reviewFeedItem->import_id = $feedImportId;
                        $reviewFeedItem->site_id = $siteId;
                        $itemTitle = $feedItem['title'];
                        $reviewFeedItem->item_title = $itemTitle;

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
                            $reviewFeedItem->item_url = $itemUrl;
                        }

                        // Date
                        $itemDateModel = new Carbon($feedItem['published']);
                        $itemDate = $itemDateModel->format('Y-m-d H:i:s');
                        $reviewFeedItem->item_date = $itemDate;

                        // Check if it's already been imported
                        $dbExistingItem = $serviceReviewFeedItem->getByItemUrl($itemUrl);
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
                        if ($reviewFeedItem->isHistoric() && !$reviewSite->allowHistoric()) {
                            $logger->warn('Skipping historic review: '.$itemUrl.' - Date: '.$itemDate);
                            continue;
                        }

                        // Output some details
                        $logger->info('Importing item with date: '.$itemDate.'; URL: '.$itemUrl);

                        // All good - add it as a feed item
                        $reviewFeedItem->load_status = 'Loaded OK';
                        $reviewFeedItem->save();

                    }

                }

            } catch (\Exception $e) {
                $logger->error('Got error: '.$e->getMessage().'; skipping');
            }

        }

    }
}
