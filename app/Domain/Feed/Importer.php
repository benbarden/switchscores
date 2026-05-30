<?php


namespace App\Domain\Feed;

use Carbon\Carbon;
use GuzzleHttp\Client as GuzzleClient;

use App\Models\ReviewDraft;
use App\Models\ReviewSite;
use App\Models\PartnerFeedLink;
use App\Domain\Game\Repository as RepoGame;
use App\Domain\ReviewFeedItem\Repository as RepoReviewFeedItem;
use App\Exceptions\Review\AlreadyImported;
use App\Exceptions\Review\FeedUrlPrefixNotMatched;
use App\Exceptions\Review\HistoricEntry;
use App\Exceptions\Review\TitleRuleNotMatched;
use App\Services\Game\TitleMatch as ServiceTitleMatch;
use App\Services\ReviewFeedItemService;
use App\Services\UrlService;


/**
 * Should be safe to delete
 * @deprecated
 */
class Importer
{
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
     * @param $feedItem
     * @return ReviewDraft
     */
    public function generateBasicModel($feedItem)
    {
        $reviewDraft = new ReviewDraft();
        $reviewDraft->site_id = $this->siteId;
        return $reviewDraft;
    }

    /**
     * @param $feedItem
     * @return ReviewDraft
     */
    public function generateModel($reviewDraft, $feedItem)
    {
        if ($this->parseAsObjects) {

            $reviewDraft->item_url = $feedItem->link;

            $itemTitle = (string) $feedItem->title;

            $pubDate = $feedItem->pubDate;
            $pubDateModel = new Carbon($pubDate);

            if (property_exists($feedItem, 'score')) {
                $reviewDraft->item_rating = $feedItem->score;
            } elseif (property_exists($feedItem, 'note')) {
                $reviewDraft->item_rating = $feedItem->note;
            }

        } else {

            $reviewDraft->item_url = $feedItem['link'];

            $itemTitle = $feedItem['title'];
            $itemTitle = str_replace('<![CDATA[', '', $itemTitle);
            $itemTitle = str_replace(']]>', '', $itemTitle);
            $itemTitle = str_replace("\r", '', $itemTitle);
            $itemTitle = str_replace("\n", '', $itemTitle);

            $pubDate = $feedItem['pubDate'];
            $pubDateModel = new Carbon($pubDate);

            if (array_key_exists('score', $feedItem)) {
                $reviewDraft->item_rating = $feedItem['score'];
            } elseif (array_key_exists('note', $feedItem)) {
                $reviewDraft->item_rating = $feedItem['note'];
            }

        }

        $reviewDraft->item_title = $itemTitle;
        $reviewDraft->item_date = $pubDateModel->format('Y-m-d H:i:s');

        return $reviewDraft;
    }

    public function processItemRss($feedItem)
    {
        // Generate the model
        $reviewDraft = $this->generateBasicModel($feedItem);
        $reviewDraft = $this->generateModel($reviewDraft, $feedItem);
        $itemTitle = $reviewDraft->item_title;
        $itemUrl = $reviewDraft->item_url;
        $itemDate = $reviewDraft->item_date;

        // Clean up URL
        $itemUrl = $this->serviceUrl->cleanReviewFeedUrl($itemUrl);
        $reviewDraft->item_url = $itemUrl;

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
        if ($reviewDraft->isHistoric() && !$this->partnerFeedLink->allowHistoric()) {
            throw new HistoricEntry('Skipping historic review: ' . $itemUrl . ' - Date: ' . $itemDate);
        }

        // Check if a feed URL prefix is set, and if so, compare it
        $feedUrlPrefix = $this->partnerFeedLink->feed_url_prefix;
        if ($feedUrlPrefix) {
            $fullPrefix = $this->reviewSite->website_url.$feedUrlPrefix;
            if (!str_starts_with($itemUrl, $fullPrefix)) {
                throw new FeedUrlPrefixNotMatched('Does not match feed URL prefix: '.$itemUrl.' - Date: '.$itemDate);
            }
        }

        // All good - add it as a feed item
        $reviewDraft->load_status = 'Loaded OK';
        return $reviewDraft;
    }

    public function processItemAtom($feedItem)
    {
        // Generate the model
        $reviewDraft = $this->generateBasicModel($feedItem);

        $itemTitle = $feedItem['title'];
        $reviewDraft->item_title = $itemTitle;

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
            $reviewDraft->item_url = $itemUrl;
        }

        // Date
        $itemDateModel = new Carbon($feedItem['published']);
        $itemDate = $itemDateModel->format('Y-m-d H:i:s');
        $reviewDraft->item_date = $itemDate;

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
            if (!str_starts_with($itemUrl, $fullPrefix)) {
                throw new FeedUrlPrefixNotMatched('Does not match feed URL prefix: '.$itemUrl.' - Date: '.$itemDate);
            }
        }

        // Check that it's not a historic review
        if ($reviewDraft->isHistoric() && !$this->partnerFeedLink->allowHistoric()) {
            throw new HistoricEntry('Skipping historic review: '.$itemUrl.' - Date: '.$itemDate);
        }

        // All good - add it as a review draft
        $reviewDraft->load_status = 'Loaded OK';
        return $reviewDraft;
    }

}