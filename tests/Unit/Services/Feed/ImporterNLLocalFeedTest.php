<?php

namespace Tests\Unit\Services\Feed;

use App\FeedItemReview;
use App\Partner;
use Illuminate\Support\Collection;
use Tests\TestCase;
#use Illuminate\Foundation\Testing\DatabaseMigrations;
#use Illuminate\Foundation\Testing\DatabaseTransactions;

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

            $feedItemReview = $this->feedImporter->generateModel(false, $feedItem);

            // Check model type
            $this->assertInstanceOf(FeedItemReview::class, $feedItemReview);

            // Compare model against feed data
            $feedLink = $feedData['channel']['item'][$counter]['link'];
            $this->assertEquals($feedLink, $feedItemReview->item_url);

            $feedTitle = $feedData['channel']['item'][$counter]['title'];
            $this->assertEquals($feedTitle, $feedItemReview->item_title);

            $feedDate = $feedData['channel']['item'][$counter]['pubDate'];
            $feedDate = date('Y-m-d H:i:s', strtotime($feedDate));
            $this->assertEquals($feedDate, $feedItemReview->item_date);

            $feedScore = $feedData['channel']['item'][$counter]['score'];
            $this->assertEquals($feedScore, $feedItemReview->item_rating);

            $counter++;

        }
    }

}
