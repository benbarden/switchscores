<?php

namespace App\Domain\Feed;

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
        $body = (new FeedFetcher())->fetch($feedUrl);

        $this->loadFromBody($body);
    }

    /**
     * Parses a body that has already been fetched.
     *
     * Split out from loadByUrl so a caller holding a response can parse it more than once
     * without re-requesting it. The probe needs exactly that: the same body has to be read
     * under different parse modes to work out which one a feed needs, and hitting a
     * partner's server once per attempt would be rude and slow.
     *
     * @param string $body
     */
    public function loadFromBody($body)
    {
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
                    foreach ($this->asItemList($this->feedData['channel']['item']) as $feedItem) {
                        $feedItemsToProcess[] = $feedItem;
                    }
                    break;
                case PartnerFeedLink::ITEM_NODE_ITEM:
                    foreach ($this->asItemList($this->feedData['item']) as $feedItem) {
                        $feedItemsToProcess[] = $feedItem;
                    }
                    break;
                case PartnerFeedLink::ITEM_NODE_ENTRY:
                    foreach ($this->asItemList($this->feedData['entry']) as $feedItem) {
                        $feedItemsToProcess[] = $feedItem;
                    }
                    break;
            }

        }

        return $feedItemsToProcess;
    }

    /**
     * Guarantees a list of items, whatever the feed contained.
     *
     * Array mode gets its data from json_encode() of a SimpleXML document, which has no way
     * to express "a list of one". A feed holding a single item decodes to that item's own
     * fields - ['title' => ..., 'link' => ...] - rather than [0 => ['title' => ...]], so
     * iterating it yields the field values as though each were an item. The importer then
     * treats four strings as four reviews.
     *
     * Rare, because feeds normally carry a page of items, but it is exactly the shape a new
     * or quiet partner feed arrives in, which is when nobody is watching closely. Object mode
     * is unaffected: SimpleXML iterates a single child correctly.
     */
    private function asItemList($items)
    {
        if (!is_array($items)) {
            return [];
        }

        // A list has sequential integer keys; a single decoded item has its element names.
        if (array_keys($items) !== range(0, count($items) - 1)) {
            return [$items];
        }

        return $items;
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