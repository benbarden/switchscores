<?php

namespace Tests\Unit\Services\Feed;

use Tests\TestCase;

use App\ReviewFeedItem;
use App\Partner;

use App\Services\Feed\Importer;

class ImporterNLLocalFeedTest extends TestCase
{
    /**
     * @var Importer
     */
    private $feedImporter;

    public function setUp(): void
    {
        $this->feedImporter = new Importer();

        $this->feedImporter->loadLocalFeedData('nintendo-life.xml');
        $this->feedImporter->setSiteId(Partner::SITE_NINTENDO_LIFE);

        parent::setUp();
    }

    public function tearDown(): void
    {
        unset($this->feedImporter);

        parent::tearDown();
    }

    public function testFeedLoaded()
    {
        $this->assertNotEmpty($this->feedImporter->getFeedData());
    }

    public function testFeedKeyChannel()
    {
        $this->assertArrayHasKey('channel', $this->feedImporter->getFeedData());
    }

    /**
     * @depends testFeedKeyChannel
     */
    public function testFeedKeyItem()
    {
        $feedData = $this->feedImporter->getFeedData();

        $this->assertArrayHasKey('item', $feedData['channel']);
    }

    /**
     * @depends testFeedKeyChannel
     */
    public function testFeedKeyItemCount()
    {
        $feedData = $this->feedImporter->getFeedData();

        $this->assertCount(20, $feedData['channel']['item']);
    }

    /**
     * @depends testFeedKeyChannel
     */
    public function testFeedModelGeneration()
    {
        $feedData = $this->feedImporter->getFeedData();

        $counter = 0;

        foreach ($feedData['channel']['item'] as $feedItem) {

            $reviewFeedItem = $this->feedImporter->generateModel($feedItem);

            // Check model type
            $this->assertInstanceOf(ReviewFeedItem::class, $reviewFeedItem);

            // Compare model against feed data
            $feedLink = $feedData['channel']['item'][$counter]['link'];
            $this->assertEquals($feedLink, $reviewFeedItem->item_url);

            $feedTitle = $feedData['channel']['item'][$counter]['title'];
            $this->assertEquals($feedTitle, $reviewFeedItem->item_title);

            $feedDate = $feedData['channel']['item'][$counter]['pubDate'];
            $feedDate = date('Y-m-d H:i:s', strtotime($feedDate));
            $this->assertEquals($feedDate, $reviewFeedItem->item_date);

            $feedScore = $feedData['channel']['item'][$counter]['score'];
            $this->assertEquals($feedScore, $reviewFeedItem->item_rating);

            $counter++;

        }
    }

}
