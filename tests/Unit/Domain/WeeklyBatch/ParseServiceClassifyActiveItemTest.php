<?php

namespace Tests\Unit\Domain\WeeklyBatch;

use App\Domain\WeeklyBatch\ParseService;
use App\Models\WeeklyBatchItem;
use Tests\TestCase;

class ParseServiceClassifyActiveItemTest extends TestCase
{
    private ParseService $parseService;

    public function setUp(): void
    {
        parent::setUp();
        $this->parseService = $this->app->make(ParseService::class);
    }

    public function tearDown(): void
    {
        unset($this->parseService);
        parent::tearDown();
    }

    // ---- Starting status ----

    public function testItemWithUrlGoesStraightToFetch()
    {
        $data = $this->parseService->classifyActiveItem(
            'Some Ordinary Game',
            29.99,
            'https://www.nintendo.com/en-gb/some-ordinary-game'
        );

        $this->assertEquals(WeeklyBatchItem::STATUS_FETCH_PENDING, $data['item_status']);
        $this->assertEquals(WeeklyBatchItem::FETCH_STATUS_PENDING, $data['fetch_status']);
    }

    public function testItemWithoutUrlWaitsForOne()
    {
        $data = $this->parseService->classifyActiveItem('Some Ordinary Game', 29.99, null);

        $this->assertEquals(WeeklyBatchItem::STATUS_PENDING, $data['item_status']);
        $this->assertArrayNotHasKey('fetch_status', $data);
    }

    // ---- LQ and bundle classification ----

    public function testAutoLqTitleIsMarkedLowQuality()
    {
        $data = $this->parseService->classifyActiveItem(
            'Korean Drone Flying Tour: Seoul',
            1.99,
            'https://www.nintendo.com/en-gb/korean-drone-flying-tour'
        );

        $this->assertEquals(WeeklyBatchItem::STATUS_LOW_QUALITY, $data['item_status']);
        $this->assertEquals(1, $data['lq_flag']);
    }

    public function testBundleTitleIsMarkedBundle()
    {
        $data = $this->parseService->classifyActiveItem(
            'Big Adventure Bundle',
            49.99,
            'https://www.nintendo.com/en-gb/big-adventure-bundle'
        );

        $this->assertEquals(WeeklyBatchItem::STATUS_BUNDLE, $data['item_status']);
    }

    public function testLqSignalFlagsItemWithoutBlockingIt()
    {
        $data = $this->parseService->classifyActiveItem(
            'Bus Driving Simulator',
            19.99,
            'https://www.nintendo.com/en-gb/bus-driving-simulator'
        );

        $this->assertEquals(WeeklyBatchItem::STATUS_FETCH_PENDING, $data['item_status']);
        $this->assertEquals(1, $data['lq_flag']);
        $this->assertEquals("Title contains 'Simulator'", $data['lq_flag_reason']);
    }

    public function testVeryLowPriceIsFlagged()
    {
        $data = $this->parseService->classifyActiveItem('Some Ordinary Game', 0.99, null);

        $this->assertEquals(1, $data['lq_flag']);
        $this->assertEquals('Very low price: £0.99', $data['lq_flag_reason']);
    }

    public function testCleanItemIsNotFlagged()
    {
        $data = $this->parseService->classifyActiveItem('Some Ordinary Game', 29.99, null);

        $this->assertEquals(0, $data['lq_flag']);
        $this->assertNull($data['lq_flag_reason']);
    }

    // ---- Collections ----

    public function testCollectionPrefixIsMatched()
    {
        $data = $this->parseService->classifyActiveItem('Arcade Archives Some Old Game', 6.29, null);

        $this->assertEquals('arcade-archives', $data['collection']);
    }

    public function testNonCollectionTitleHasNoCollection()
    {
        $data = $this->parseService->classifyActiveItem('Some Ordinary Game', 29.99, null);

        $this->assertNull($data['collection']);
    }
}
