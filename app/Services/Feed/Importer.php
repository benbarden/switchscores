<?php


namespace App\Services\Feed;

use App\FeedItemReview;
use App\Partner;
use App\Services\FeedItemReviewService;
use App\Services\UrlService;
use Carbon\Carbon;

use GuzzleHttp\Client as GuzzleClient;

use App\Exceptions\Review\AlreadyImported;
use App\Exceptions\Review\HistoricEntry;
use App\Exceptions\Review\FeedUrlPrefixNotMatched;


class Importer
{
    /**
     * @var array
     */
    private $feedData;

    /**
     * @var integer
     */
    private $siteId;

    /**
     * @param integer $siteId
     * @return void
     */
    public function setSiteId($siteId)
    {
        $this->siteId = $siteId;
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
     * @param bool $isWix
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function loadRemoteFeedData($feedUrl, $isWix = false)
    {
        try {
            $client = new GuzzleClient(['verify' => false]);
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
            $this->feedData = $this->convertResponseToJson($body, $isWix);
        } catch (\Exception $e) {
            throw new \Exception('Error loading data! Error details: '.$e->getMessage().'; Raw data: '.$body);
        }
    }

    /**
     * @param $body
     * @param $isWix
     * @return mixed
     */
    public function convertResponseToJson($body, $isWix = false)
    {
        if ($isWix) {
            // Don't do the JSON conversion for Wix sites - it breaks the SimpleXMLElements
            $xmlObject = simplexml_load_string($body);
            $decodedJson = $xmlObject;
        } else {
            $xmlObject = simplexml_load_string($body);
            $encodedJson = json_encode($xmlObject);
            $decodedJson = json_decode($encodedJson, true);
        }
        return $decodedJson;
    }

    /**
     * @param $isWix
     * @param $feedItem
     * @return FeedItemReview
     */
    public function generateModel($isWix, $feedItem)
    {
        $feedItemReview = new FeedItemReview();

        // Basic fields
        $feedItemReview->site_id = $this->siteId;

        if ($isWix) {

            $feedItemReview->item_url = $feedItem->link;

            $itemTitle = (string) $feedItem->title;

            $pubDate = $feedItem->pubDate;
            $pubDateModel = new Carbon($pubDate);

        } else {

            $feedItemReview->item_url = $feedItem['link'];

            $itemTitle = $feedItem['title'];
            $itemTitle = str_replace('<![CDATA[', '', $itemTitle);
            $itemTitle = str_replace(']]>', '', $itemTitle);
            $itemTitle = str_replace("\r", '', $itemTitle);
            $itemTitle = str_replace("\n", '', $itemTitle);

            $pubDate = $feedItem['pubDate'];
            $pubDateModel = new Carbon($pubDate);

            if (array_key_exists('score', $feedItem)) {
                $feedItemReview->item_rating = $feedItem['score'];
            }

        }

        $feedItemReview->item_title = $itemTitle;
        $feedItemReview->item_date = $pubDateModel->format('Y-m-d H:i:s');

        return $feedItemReview;
    }

    public function processItemRss($isWix, $feedItem, Partner $reviewSite, UrlService $serviceUrl, FeedItemReviewService $serviceFeedItemReview)
    {
        // Generate the model
        $feedItemReview = $this->generateModel($isWix, $feedItem);
        $itemTitle = $feedItemReview->item_title;
        $itemUrl = $feedItemReview->item_url;
        $itemDate = $feedItemReview->item_date;

        // Clean up URL
        $itemUrl = $serviceUrl->cleanReviewFeedUrl($itemUrl);
        $feedItemReview->item_url = $itemUrl;

        // Check if it's already been imported
        $dbExistingItem = $serviceFeedItemReview->getByItemUrl($itemUrl);
        if ($dbExistingItem) {
            throw new AlreadyImported('Already imported: '.$itemUrl);
        }

        // Silently bypass historic reviews - removes some log noise
        if ($feedItemReview->isHistoric() && !$reviewSite->allowHistoric()) {
            throw new HistoricEntry('Skipping historic review: '.$itemUrl.' - Date: '.$itemDate);
        }

        // Check if a feed URL prefix is set, and if so, compare it
        $feedUrlPrefix = $reviewSite->feed_url_prefix;
        if ($feedUrlPrefix) {
            $fullPrefix = $reviewSite->website_url.$feedUrlPrefix;
            if (substr($itemUrl, 0, strlen($fullPrefix)) != $fullPrefix) {
                throw new FeedUrlPrefixNotMatched('Does not match feed URL prefix: '.$itemUrl.' - Date: '.$itemDate);
            }
        }

        // All good - add it as a feed item
        $feedItemReview->load_status = 'Loaded OK';
        return $feedItemReview;
    }

    public function processItemAtom($feedItem)
    {

    }
}