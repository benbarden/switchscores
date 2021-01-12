<?php


namespace App\Services\PartnerFeedLink;

use App\Exceptions\Review\AlreadyImported;
use App\Exceptions\Review\FeedUrlPrefixNotMatched;
use App\Exceptions\Review\HistoricEntry;
use App\Exceptions\Review\TitleRuleNotMatched;
use App\PartnerFeedLink;
use App\ReviewFeedImport;
use App\Services\Feed\Importer;
use App\Services\UrlService;
use App\Traits\SwitchServices;

class ImportReviewFeed
{
    use SwitchServices;

    private $logger;

    /**
     * @var PartnerFeedLink
     */
    private $partnerFeedLink;

    /**
     * @var ReviewFeedImport
     */
    private $reviewFeedImport;

    /**
     * @var boolean
     */
    private $isTest;

    public function __construct($logger)
    {
        $this->logger = $logger;
        $this->isTest = false;
    }

    public function setPartnerFeedLink(PartnerFeedLink $partnerFeedLink)
    {
        if (!$partnerFeedLink->feed_url) {
            $this->logger->error('Feed url cannot be empty!');
            throw new \Exception('Feed url cannot be empty!');
        }

        $this->partnerFeedLink = $partnerFeedLink;
    }

    public function setIsTest($isTest)
    {
        $this->isTest = $isTest;
    }

    public function clearPreviousTests()
    {
        if ($this->isTest) {
            $this->logger->info('***** TEST MODE *****');
            $this->getServiceReviewFeedItemTest()->deleteTestItemsBySite($this->partnerFeedLink->site_id);
        }
    }

    public function createBatchFeedImport()
    {
        $reviewFeedImport = $this->getServiceReviewFeedImport()->createCron(null, null, $this->isTest);
        $this->reviewFeedImport = $reviewFeedImport;
        return $reviewFeedImport;
    }

    public function createFeedImport()
    {
        $feedId = $this->partnerFeedLink->id;
        $siteId = $this->partnerFeedLink->site_id;
        $reviewFeedImport = $this->getServiceReviewFeedImport()->createSiteCron($feedId, $siteId, $this->isTest);
        $this->reviewFeedImport = $reviewFeedImport;
        return $reviewFeedImport;
    }

    public function setFeedImport(ReviewFeedImport $reviewFeedImport)
    {
        $this->reviewFeedImport = $reviewFeedImport;
    }

    public function runImport()
    {
        $siteId = $this->partnerFeedLink->site_id;
        $partnerFeedUrl = $this->partnerFeedLink->feed_url;
        $reviewSite = $this->getServicePartner()->find($siteId);
        $partnerName = $reviewSite->name;

        $serviceReviewFeedItem = $this->getServiceReviewFeedItem();
        $serviceUrl = new UrlService();

        $this->logger->info(sprintf('Site %s: %s - Feed URL: %s', $siteId, $partnerName, $partnerFeedUrl));

        try {

            // Set up the importer for this feed
            $feedImporter = new Importer();
            if ($this->isTest) {
                $feedImporter->setTestMode();
            }
            if ($this->partnerFeedLink->isParseAsObjects()) {
                $feedImporter->setParseAsObjects(true);
            }
            $feedImporter->loadRemoteFeedData($partnerFeedUrl);
            $feedImporter->setSiteId($siteId);
            $feedData = $feedImporter->getFeedData();
            $feedItemsToProcess = $feedImporter->generateItemsArray($this->partnerFeedLink);
            // Flip the order, as we should import oldest to newest
            $feedItemsToProcess = array_reverse($feedItemsToProcess);

            $successCount = 0;
            $failureCount = 0;

            $feedImporter->setReviewSite($reviewSite);
            $feedImporter->setPartnerFeedLink($this->partnerFeedLink);
            $feedImporter->setServiceUrl($serviceUrl);
            $feedImporter->setServiceReviewFeedItem($serviceReviewFeedItem);

            foreach ($feedItemsToProcess as $feedItem) {

                try {
                    if ($this->partnerFeedLink->isAtom()) {
                        $reviewFeedItem = $feedImporter->processItemAtom($feedItem);
                    } else {
                        $reviewFeedItem = $feedImporter->processItemRss($feedItem);
                    }
                    $this->logger->info('Importing item with date: '.$reviewFeedItem->item_date.'; URL: '.$reviewFeedItem->item_url);
                    $reviewFeedItem->import_id = $this->reviewFeedImport->id;
                    $reviewFeedItem->save();
                    $successCount++;
                } catch (AlreadyImported $e) {
                    $failureCount++;
                } catch (HistoricEntry $e) {
                    $failureCount++;
                } catch (FeedUrlPrefixNotMatched $e) {
                    $failureCount++;
                } catch (TitleRuleNotMatched $e) {
                    $failureCount++;
                }

            }

            $this->partnerFeedLink->was_last_run_successful = 1;
            $this->partnerFeedLink->last_run_status = "Imported: $successCount - Skipped: $failureCount";
            $this->partnerFeedLink->save();

        } catch (\Exception $e) {

            $this->logger->error('Got error: '.$e->getMessage());

            $this->partnerFeedLink->was_last_run_successful = 0;
            $this->partnerFeedLink->last_run_status = $e->getMessage();
            $this->partnerFeedLink->save();

        }
    }
}