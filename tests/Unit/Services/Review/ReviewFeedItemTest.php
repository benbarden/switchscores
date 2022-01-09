<?php

namespace Tests\Unit\Services\Review;

use App\Models\Partner;
use App\ReviewFeedItem;
use Carbon\Carbon;
use Tests\TestCase;

class ReviewFeedItemTest extends TestCase
{
    private function generateModel(\DateTime $dtItemDate)
    {
        $reviewFeedItem = new ReviewFeedItem();

        // Basic fields
        $reviewFeedItem->site_id = Partner::SITE_WOS;
        $reviewFeedItem->item_url = '/abc';
        $reviewFeedItem->item_title = 'Test Abc';

        // Date
        $pubDate = $dtItemDate->format('Y-m-d H:i:s');
        $pubDateModel = new Carbon($pubDate);
        $reviewFeedItem->item_date = $pubDateModel->format('Y-m-d H:i:s');

        return $reviewFeedItem;
    }

    public function testIsHistoricNow()
    {
        $dtItemDate = new \DateTime("now");
        $reviewFeedItem = $this->generateModel($dtItemDate);
        $this->assertEquals(false, $reviewFeedItem->isHistoric());
    }

    public function testIsHistoric7Days()
    {
        $dtItemDate = new \DateTime("now -7 days");
        $reviewFeedItem = $this->generateModel($dtItemDate);
        $this->assertEquals(false, $reviewFeedItem->isHistoric());
    }

    public function testIsHistoric14Days()
    {
        $dtItemDate = new \DateTime("now -14 days");
        $reviewFeedItem = $this->generateModel($dtItemDate);
        $this->assertEquals(false, $reviewFeedItem->isHistoric());
    }

    public function testIsHistoric21Days()
    {
        $dtItemDate = new \DateTime("now -21 days");
        $reviewFeedItem = $this->generateModel($dtItemDate);
        $this->assertEquals(false, $reviewFeedItem->isHistoric());
    }

    public function testIsHistoric28Days()
    {
        $dtItemDate = new \DateTime("now -28 days");
        $reviewFeedItem = $this->generateModel($dtItemDate);
        $this->assertEquals(false, $reviewFeedItem->isHistoric());
    }

    public function testIsHistoric30Days()
    {
        $dtItemDate = new \DateTime("now -30 days");
        $reviewFeedItem = $this->generateModel($dtItemDate);
        $this->assertEquals(false, $reviewFeedItem->isHistoric());
    }

    public function testIsHistoric31Days()
    {
        $dtItemDate = new \DateTime("now -31 days");
        $reviewFeedItem = $this->generateModel($dtItemDate);
        $this->assertEquals(true, $reviewFeedItem->isHistoric());
    }

    public function testIsHistoric90Days()
    {
        $dtItemDate = new \DateTime("now -90 days");
        $reviewFeedItem = $this->generateModel($dtItemDate);
        $this->assertEquals(true, $reviewFeedItem->isHistoric());
    }

}
