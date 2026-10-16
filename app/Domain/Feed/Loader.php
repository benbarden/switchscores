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

    /**
     * The response loadByUrl() fetched, kept for the run history.
     */
    private ?FeedResponse $response = null;

    public function __construct(PartnerFeedLink $partnerFeedLink)
    {
        $this->partnerFeedLink = $partnerFeedLink;
    }

    /**
     * @param $feedUrl
     * @throws FeedLoadException
     */
    public function loadByUrl($feedUrl)
    {
        $this->response = (new FeedFetcher())->fetchResponse($feedUrl);

        (new FeedResponseClassifier())->assertFeed($this->response);

        $this->loadFromBody($this->response->body);
    }

    /**
     * The response from the last loadByUrl(), or null if nothing was fetched.
     */
    public function getResponse(): ?FeedResponse
    {
        return $this->response;
    }

    /**
     * Parses a body that has already been fetched.
     *
     * Split out from loadByUrl so a caller holding a response can parse it more than once
     * without re-requesting it. The probe needs exactly that: the same body has to be read
     * under different parse modes to work out which one a feed needs, and hitting a
     * partner's server once per attempt would be rude and slow.
     *
     * The body is never added to the error message: it used to be, which put whole bot check
     * pages into the cron log and the Feed Links table. It travels on the exception instead.
     *
     * @param string $body
     * @throws FeedLoadException
     */
    public function loadFromBody($body)
    {
        try {
            $this->feedData = $this->convertResponseToJson($body);
        } catch (\Exception $e) {
            throw new FeedLoadException(
                'Invalid XML: '.FeedFetcher::firstLine($e->getMessage()),
                FeedRunOutcome::PARSE_ERROR,
                $this->response ?? new FeedResponse(null, null, (string) $body),
                $e
            );
        }
    }

    /**
     * @param $body
     * @return mixed
     */
    private function convertResponseToJson($body)
    {
        $xmlObject = simplexml_load_string($body);

        // Usually a parse failure arrives as a warning Laravel turns into an exception, but if
        // warnings are suppressed simplexml just returns false - which used to decode to false
        // and fail later, somewhere less helpful.
        if ($xmlObject === false) {
            throw new \Exception('the response could not be parsed as XML');
        }

        if ($this->partnerFeedLink->isParseAsObjects()) {
            // Don't do the JSON conversion for Wix sites or others using CDATA - it breaks the SimpleXMLElements
            $decodedJson = $xmlObject;
        } else {
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