<?php

namespace Tests\Unit\Domain\Feed;

use App\Domain\Feed\Loader;
use App\Models\PartnerFeedLink;

use Tests\TestCase;

/**
 * Covers item extraction from a body already in hand. Fetching is FeedFetcher's job and is
 * not exercised here.
 */
class LoaderTest extends TestCase
{
    private function feedLink($dataType, $itemNode = PartnerFeedLink::ITEM_NODE_CHANNEL_ITEM)
    {
        $feedLink = new PartnerFeedLink();
        $feedLink->data_type = $dataType;
        $feedLink->item_node = $itemNode;

        return $feedLink;
    }

    private function rssFeed($itemCount)
    {
        $items = '';

        for ($i = 1; $i <= $itemCount; $i++) {
            $items .= '<item>'
                .'<title>Game '.$i.' Review</title>'
                .'<link>https://example.com/'.$i.'</link>'
                .'<pubDate>Sun, 26 Jul 2026 22:59:48 +0000</pubDate>'
                .'<score max="10">8.0</score>'
                .'</item>';
        }

        return '<?xml version="1.0" encoding="UTF-8"?><rss version="2.0"><channel>'
            .'<title>Example</title><link>https://example.com/</link>'
            .$items
            .'</channel></rss>';
    }

    private function loadItems(PartnerFeedLink $feedLink, $body)
    {
        $loader = new Loader($feedLink);
        $loader->loadFromBody($body);

        return $loader->buildItemArray();
    }

    /**
     * The regression this file was created for.
     *
     * Array mode decodes the document with json_encode(), which cannot express a list of one:
     * a feed holding a single item decodes to that item's own fields, so iterating it returned
     * one entry per FIELD. A one-item feed came back as four items, each of them a bare string.
     *
     * Object mode never had the problem, which is why it went unnoticed - and why the same
     * feed could behave differently depending only on its parse mode.
     */
    public function testASingleItemFeedYieldsOneItemInArrayMode()
    {
        $items = $this->loadItems($this->feedLink(PartnerFeedLink::DATA_TYPE_ARRAY), $this->rssFeed(1));

        $this->assertCount(1, $items);
        $this->assertIsArray($items[0]);
        $this->assertEquals('Game 1 Review', $items[0]['title']);
    }

    public function testASingleItemFeedYieldsOneItemInObjectMode()
    {
        $items = $this->loadItems($this->feedLink(PartnerFeedLink::DATA_TYPE_OBJECT), $this->rssFeed(1));

        $this->assertCount(1, $items);
        $this->assertEquals('Game 1 Review', (string) $items[0]->title);
    }

    public function testMultipleItemsAreUnaffectedInArrayMode()
    {
        $items = $this->loadItems($this->feedLink(PartnerFeedLink::DATA_TYPE_ARRAY), $this->rssFeed(3));

        $this->assertCount(3, $items);
        $this->assertEquals('Game 1 Review', $items[2]['title']);
    }

    /**
     * Items are reversed so the importer works oldest to newest. Pinned because the single
     * item fix touches the same method, and a list that silently stopped being reversed would
     * change which reviews are treated as historic.
     */
    public function testItemsAreReturnedOldestFirst()
    {
        $items = $this->loadItems($this->feedLink(PartnerFeedLink::DATA_TYPE_ARRAY), $this->rssFeed(3));

        $this->assertEquals('Game 3 Review', $items[0]['title']);
        $this->assertEquals('Game 1 Review', $items[2]['title']);
    }

    public function testAnItemNodeTheParseModeCannotWalkYieldsNoItems()
    {
        $items = $this->loadItems(
            $this->feedLink(PartnerFeedLink::DATA_TYPE_OBJECT, PartnerFeedLink::ITEM_NODE_ENTRY),
            $this->rssFeed(3)
        );

        $this->assertCount(0, $items);
    }
}
