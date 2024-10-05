<?php

namespace App\Domain\Feed;

use GuzzleHttp\Client as GuzzleClient;

use App\Models\PartnerFeedLink;

class Loader
{
    /**
     * @var PartnerFeedLink
     */
    private $partnerFeedLink;

    /**
     * @var array
     */
    private $feedData;

    public function __construct(PartnerFeedLink $partnerFeedLink)
    {
        $this->partnerFeedLink = $partnerFeedLink;
    }

    /**
     * @param $feedUrl
     * @param bool $parseAsObjects
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function loadByUrl($feedUrl)
    {
        try {
            $client = new GuzzleClient(
                [
                    'headers' => [
                        'User-Agent' => 'switchscores/v2.0',
                        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
                        'Accept-Encoding' => 'gzip, deflate',
                    ],
                    // https://serverfault.com/questions/1126612/ssl-error-unexpected-eof-while-reading-on-same-server-as-the-originating-reque
                    'config' => [
                        'curl' => [
                            'CURLOPT_SSLVERSION' => 'CURL_SSLVERSION_TLSv1_2',
                            'CURLOPT_SSL_CIPHER_LIST' => 'AES256+EECDH:AES256+EDH',
                        ],
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
    private function convertResponseToJson($body)
    {
        if ($this->partnerFeedLink->isParseAsObjects()) {
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

    /**
     * @return array
     */
    private function generateItemsArray()
    {
        $feedItemsToProcess = [];

        if ($this->partnerFeedLink->isParseAsObjects()) {

            switch ($this->partnerFeedLink->item_node) {

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

            switch ($this->partnerFeedLink->item_node) {

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
     * @return array
     */
    public function buildItemArray()
    {
        $itemArray = $this->generateItemsArray();
        // Flip the order, as we should import oldest to newest
        $itemArray = array_reverse($itemArray);
        return $itemArray;
    }
}