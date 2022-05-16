<?php


namespace App\Services\Feed;


use Carbon\Carbon;
use GuzzleHttp\Client as GuzzleClient;

use App\Models\ReviewSite;
use App\Models\PartnerFeedLink;
use App\Models\ReviewFeedItem;
use App\Models\ReviewFeedItemTest;
use App\Domain\Game\Repository as RepoGame;
use App\Domain\ReviewFeedItem\Repository as RepoReviewFeedItem;
use App\Exceptions\Review\AlreadyImported;
use App\Exceptions\Review\FeedUrlPrefixNotMatched;
use App\Exceptions\Review\HistoricEntry;
use App\Exceptions\Review\TitleRuleNotMatched;
use App\Services\Game\TitleMatch as ServiceTitleMatch;
use App\Services\ReviewFeedItemService;
use App\Services\UrlService;


class Importer
{
    /**
     * @var boolean
     */
    private $isTestMode;

    /**
     * @var boolean
     */
    private $parseAsObjects;

    /**
     * @var array
     */
    private $feedData;

    /**
     * @var integer
     */
    private $siteId;

    /**
     * @var ReviewSite
     */
    private $reviewSite;

    /**
     * @var \App\Models\PartnerFeedLink
     */
    private $partnerFeedLink;

    /**
     * @var UrlService
     */
    private $serviceUrl;

    /**
     * @var ReviewFeedItemService
     */
    private $serviceReviewFeedItem;

    public function __construct()
    {
        $this->repoGame = new RepoGame();
        $this->repoReviewFeedItem = new RepoReviewFeedItem();
        $this->isTestMode = false;
        $this->parseAsObjects = false;
    }

    /**
     * @param integer $siteId
     * @return void
     */
    public function setSiteId($siteId)
    {
        $this->siteId = $siteId;
    }

    public function setReviewSite(ReviewSite $reviewSite)
    {
        $this->reviewSite = $reviewSite;
    }

    public function setPartnerFeedLink(PartnerFeedLink $partnerFeedLink)
    {
        $this->partnerFeedLink = $partnerFeedLink;
    }

    public function setServiceUrl(UrlService $serviceUrl)
    {
        $this->serviceUrl = $serviceUrl;
    }

    public function setServiceReviewFeedItem(ReviewFeedItemService $serviceReviewFeedItem)
    {
        $this->serviceReviewFeedItem = $serviceReviewFeedItem;
    }

    /**
     * @return array
     */
    public function getFeedData()
    {
        return $this->feedData;
    }

    /**
     * @param array $data
     * @return void
     */
    public function setStubFeedData($data)
    {
        $this->feedData = $data;

        $this->siteId = 0;
    }

    public function setTestMode()
    {
        $this->isTestMode = true;
    }

    public function setParseAsObjects($parseAsObjects = true)
    {
        $this->parseAsObjects = $parseAsObjects;
    }

    /**
     * @return void
     */
    public function loadLocalFeedData($feedName)
    {
        $xmlData = file_get_contents(dirname(__FILE__).'/../../../storage/feeds/'.$feedName);

        $this->feedData = json_decode(json_encode(simplexml_load_string($xmlData)), true);
    }

    /**
     * @param $feedUrl
     * @param bool $parseAsObjects
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function loadRemoteFeedData($feedUrl)
    {
        try {
            $client = new GuzzleClient(
                [
                    'headers' => [
                        'User-Agent' => 'switchscores/v1.0',
                        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
                        'Accept-Encoding' => 'gzip, deflate',
                    ],
                    'verify' => false
                ]
            );
            $response = $client->request('GET', $feedUrl);
        } catch (\Exception $e) {
            throw new \Exception('Failed to load feed URL! Error: '.$e->getMessage());
        }

        try {
            $statusCode = $response->getStatusCode();
            $body = $response->getBody();

        } catch (\Exception $e) {
            throw new \Exception('Failed to load feed URL! Status code: '.$statusCode.'Error: '.$e->getMessage());
        }

        if ($statusCode != 200) {
            throw new \Exception('Cannot load feed: '.$feedUrl);
        }

        try {
            $this->feedData = $this->convertResponseToJson($body);
        } catch (\Exception $e) {
            throw new \Exception('Error loading data! Error details: '.$e->getMessage().'; Raw data: '.$body);
        }
    }

    /**
     * @param $body
     * @return mixed
     */
    public function convertResponseToJson($body)
    {
        if ($this->parseAsObjects) {
            // Don't do the JSON conversion for Wix sites or others using CDATA - it breaks the SimpleXMLElements
            $xmlObject = simplexml_load_string($body);
            $decodedJson = $xmlObject;
        } else {
            $xmlObject = simplexml_load_string($body);
            $encodedJson = json_encode($xmlObject);
            $decodedJson = json_decode($encodedJson, true);
        }
        return $decodedJson;
    }

    public function generateItemsArray(PartnerFeedLink $partnerFeedLink)
    {
        $feedItemsToProcess = [];

        if ($partnerFeedLink->isParseAsObjects()) {

            switch ($partnerFeedLink->item_node) {

                case PartnerFeedLink::ITEM_NODE_CHANNEL_ITEM:
                    foreach ($this->feedData->channel->item as $feedItem) {
                        $feedItemsToProcess[] = $feedItem;
                    }
                    break;
                case PartnerFeedLink::ITEM_NODE_POST:
                    foreach ($this->feedData->post as $feedItem) {
                        $feedItemsToProcess[] = $feedItem;
                    }
                    break;
            }

        } else {

            switch ($partnerFeedLink->item_node) {

                case PartnerFeedLink::ITEM_NODE_CHANNEL_ITEM:
                    foreach ($this->feedData['channel']['item'] as $feedItem) {
                        $feedItemsToProcess[] = $feedItem;
                    }
                    break;
                case PartnerFeedLink::ITEM_NODE_ITEM:
                    foreach ($this->feedData['item'] as $feedItem) {
                        $feedItemsToProcess[] = $feedItem;
                    }
                    break;
                case PartnerFeedLink::ITEM_NODE_ENTRY:
                    foreach ($this->feedData['entry'] as $feedItem) {
                        $feedItemsToProcess[] = $feedItem;
                    }
                    break;
            }

        }

        return $feedItemsToProcess;

    }

    /**
     * @param $feedItem
     * @return ReviewFeedItem|ReviewFeedItemTest
     */
    public function generateBasicModel($feedItem)
    {
        if ($this->isTestMode) {
            $reviewFeedItem = new ReviewFeedItemTest();
        } else {
            $reviewFeedItem = new ReviewFeedItem();
        }
        $reviewFeedItem->site_id = $this->siteId;
        return $reviewFeedItem;
    }

    /**
     * @param $feedItem
     * @return ReviewFeedItem|ReviewFeedItemTest
     */
    public function generateModel($reviewFeedItem, $feedItem)
    {
        if ($this->parseAsObjects) {

            $reviewFeedItem->item_url = $feedItem->link;

            $itemTitle = (string) $feedItem->title;

            $pubDate = $feedItem->pubDate;
            $pubDateModel = new Carbon($pubDate);

            if (property_exists($feedItem, 'score')) {
                $reviewFeedItem->item_rating = $feedItem->score;
            } elseif (property_exists($feedItem, 'note')) {
                $reviewFeedItem->item_rating = $feedItem->note;
            }

        } else {

            $reviewFeedItem->item_url = $feedItem['link'];

            $itemTitle = $feedItem['title'];
            $itemTitle = str_replace('<![CDATA[', '', $itemTitle);
            $itemTitle = str_replace(']]>', '', $itemTitle);
            $itemTitle = str_replace("\r", '', $itemTitle);
            $itemTitle = str_replace("\n", '', $itemTitle);

            $pubDate = $feedItem['pubDate'];
            $pubDateModel = new Carbon($pubDate);

            if (array_key_exists('score', $feedItem)) {
                $reviewFeedItem->item_rating = $feedItem['score'];
            } elseif (array_key_exists('note', $feedItem)) {
                $reviewFeedItem->item_rating = $feedItem['note'];
            }

        }

        $reviewFeedItem->item_title = $itemTitle;
        $reviewFeedItem->item_date = $pubDateModel->format('Y-m-d H:i:s');

        return $reviewFeedItem;
    }

    public function processItemRss($feedItem)
    {
        // Generate the model
        $reviewFeedItem = $this->generateBasicModel($feedItem);
        $reviewFeedItem = $this->generateModel($reviewFeedItem, $feedItem);
        $itemTitle = $reviewFeedItem->item_title;
        $itemUrl = $reviewFeedItem->item_url;
        $itemDate = $reviewFeedItem->item_date;

        // Clean up URL
        $itemUrl = $this->serviceUrl->cleanReviewFeedUrl($itemUrl);
        $reviewFeedItem->item_url = $itemUrl;

        // Check we have the right data available.
        // Feed fields no longer exist on the Partner/ReviewSite record.
        if (!$this->partnerFeedLink) {
            throw new \Exception('Fatal error - Cannot load partnerFeedLink for item: '.$itemUrl.' - Date: '.$itemDate);
        }

        // Check if it's already been imported
        if (!$this->isTestMode) {
            $dbExistingItem = $this->serviceReviewFeedItem->getByItemUrl($itemUrl);
            if ($dbExistingItem) {
                throw new AlreadyImported('Already imported: '.$itemUrl);
            }
        }

        // Silently bypass historic reviews - removes some log noise
        if ($reviewFeedItem->isHistoric() && !$this->partnerFeedLink->allowHistoric()) {
            throw new HistoricEntry('Skipping historic review: ' . $itemUrl . ' - Date: ' . $itemDate);
        }

        // Check if a feed URL prefix is set, and if so, compare it
        $feedUrlPrefix = $this->partnerFeedLink->feed_url_prefix;
        if ($feedUrlPrefix) {
            $fullPrefix = $this->reviewSite->website_url.$feedUrlPrefix;
            if (substr($itemUrl, 0, strlen($fullPrefix)) != $fullPrefix) {
                throw new FeedUrlPrefixNotMatched('Does not match feed URL prefix: '.$itemUrl.' - Date: '.$itemDate);
            }
        }

        // All good - add it as a feed item
        $reviewFeedItem->load_status = 'Loaded OK';
        return $reviewFeedItem;
    }

    public function processItemAtom($feedItem)
    {
        // Generate the model
        $reviewFeedItem = $this->generateBasicModel($feedItem);

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

        // Check we have the right data available.
        // Feed fields no longer exist on the Partner/ReviewSite record.
        if (!$this->partnerFeedLink) {
            throw new \Exception('Fatal error - Cannot load partnerFeedLink for item: '.$itemUrl.' - Date: '.$itemDate);
        }

        // Check if it's already been imported
        if (!$this->isTestMode) {
            $dbExistingItem = $this->serviceReviewFeedItem->getByItemUrl($itemUrl);
            if ($dbExistingItem) {
                throw new AlreadyImported('Already imported: ' . $itemUrl);
            }
        }

        // Special rules for Digitally Downloaded
        if ($this->reviewSite->name == 'Digitally Downloaded') {

            $serviceTitleMatch = new ServiceTitleMatch();

            $titleMatchRulePattern = $this->partnerFeedLink->title_match_rule_pattern;
            $titleMatchIndex = $this->partnerFeedLink->title_match_rule_index;

            if ($titleMatchRulePattern && ($titleMatchIndex != null)) {

                // New method
                $serviceTitleMatch->setMatchRule($titleMatchRulePattern);
                $serviceTitleMatch->prepareMatchRule();
                $serviceTitleMatch->setMatchIndex($titleMatchIndex);
                $parsedTitle = $serviceTitleMatch->generate($itemTitle);

                // Can we find a game from this title?
                // NB. If there's no match, we'll skip this item altogether
                if (!$parsedTitle) {
                    throw new TitleRuleNotMatched('Does not match title rule: '.$itemUrl);
                }

            }
        }

        // Check if a feed URL prefix is set, and if so, compare it
        $feedUrlPrefix = $this->partnerFeedLink->feed_url_prefix;
        if ($feedUrlPrefix) {
            $fullPrefix = $this->reviewSite->website_url.$feedUrlPrefix;
            if (substr($itemUrl, 0, strlen($fullPrefix)) != $fullPrefix) {
                throw new FeedUrlPrefixNotMatched('Does not match feed URL prefix: '.$itemUrl.' - Date: '.$itemDate);
            }
        }

        // Check that it's not a historic review
        if ($reviewFeedItem->isHistoric() && !$this->partnerFeedLink->allowHistoric()) {
            throw new HistoricEntry('Skipping historic review: '.$itemUrl.' - Date: '.$itemDate);
        }

        // All good - add it as a feed item
        $reviewFeedItem->load_status = 'Loaded OK';
        return $reviewFeedItem;
    }

}