<?php


namespace App\Services\Feed;

use App\ReviewSite;
use App\FeedItemReview;
use Carbon\Carbon;


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
     * @param string $feedUrl
     * @throws \Exception
     * @return void
     */
    public function loadRemoteFeedData($feedUrl)
    {
        // @todo Do this properly with GuzzleHttp
        $xmlData = file_get_contents($feedUrl);

        if (!$xmlData) {
            throw new \Exception('Cannot load feed: '.$feedUrl);
        }

        $this->feedData = json_decode(json_encode(simplexml_load_string($xmlData)), true);
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
        $feedItemReview->item_title = $feedItem['title'];

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