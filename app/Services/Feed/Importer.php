<?php


namespace App\Services\Feed;

use App\ReviewSite;
use App\FeedItemReview;
use Carbon\Carbon;

use GuzzleHttp\Client as GuzzleClient;


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
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function loadRemoteFeedData($feedUrl)
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
        $xmlObject = simplexml_load_string($body);
        $encodedJson = json_encode($xmlObject);
        $decodedJson = json_decode($encodedJson, true);
        return $decodedJson;
    }

    /**
     * @param $feedItem
     * @return FeedItemReview
     */
    public function generateModel($feedItem)
    {
        $feedItemReview = new FeedItemReview();

        // Basic fields
        $feedItemReview->site_id = $this->siteId;
        $feedItemReview->item_url = $feedItem['link'];

        // Clean up the title
        $itemTitle = $feedItem['title'];
        $itemTitle = str_replace('<![CDATA[', '', $itemTitle);
        $itemTitle = str_replace(']]>', '', $itemTitle);
        $itemTitle = str_replace("\r", '', $itemTitle);
        $itemTitle = str_replace("\n", '', $itemTitle);
        $feedItemReview->item_title = $itemTitle;

        // Date
        $pubDate = $feedItem['pubDate'];
        $pubDateModel = new Carbon($pubDate);
        $feedItemReview->item_date = $pubDateModel->format('Y-m-d H:i:s');

        // Score
        if (array_key_exists('score', $feedItem)) {
            $feedItemReview->item_rating = $feedItem['score'];
        }

        return $feedItemReview;
    }
}