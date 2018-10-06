<?php

namespace Tests\Unit\Services\Review;

use Illuminate\Support\Collection;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\FeedItemReview;
use App\ReviewSite;
use Carbon\Carbon;

class FeedItemReviewTest extends TestCase
{
    private function generateModel(\DateTime $dtItemDate)
    {
        $feedItemReview = new FeedItemReview();

        // Basic fields
        $feedItemReview->site_id = ReviewSite::SITE_WOS;
        $feedItemReview->item_url = '/abc';
        $feedItemReview->item_title = 'Test Abc';

        // Date
        $pubDate = $dtItemDate->format('Y-m-d H:i:s');
        $pubDateModel = new Carbon($pubDate);
        $feedItemReview->item_date = $pubDateModel->format('Y-m-d H:i:s');

        return $feedItemReview;
    }

    public function testIsHistoricNow()
    {
        $dtItemDate = new \DateTime("now");
        $feedItemReview = $this->generateModel($dtItemDate);
        $this->assertEquals(false, $feedItemReview->isHistoric());
    }

    public function testIsHistoric7Days()
    {
        $dtItemDate = new \DateTime("now -7 days");
        $feedItemReview = $this->generateModel($dtItemDate);
        $this->assertEquals(false, $feedItemReview->isHistoric());
    }

    public function testIsHistoric14Days()
    {
        $dtItemDate = new \DateTime("now -14 days");
        $feedItemReview = $this->generateModel($dtItemDate);
        $this->assertEquals(false, $feedItemReview->isHistoric());
    }

    public function testIsHistoric21Days()
    {
        $dtItemDate = new \DateTime("now -21 days");
        $feedItemReview = $this->generateModel($dtItemDate);
        $this->assertEquals(false, $feedItemReview->isHistoric());
    }

    public function testIsHistoric28Days()
    {
        $dtItemDate = new \DateTime("now -28 days");
        $feedItemReview = $this->generateModel($dtItemDate);
        $this->assertEquals(false, $feedItemReview->isHistoric());
    }

    public function testIsHistoric30Days()
    {
        $dtItemDate = new \DateTime("now -30 days");
        $feedItemReview = $this->generateModel($dtItemDate);
        $this->assertEquals(false, $feedItemReview->isHistoric());
    }

    public function testIsHistoric31Days()
    {
        $dtItemDate = new \DateTime("now -31 days");
        $feedItemReview = $this->generateModel($dtItemDate);
        $this->assertEquals(true, $feedItemReview->isHistoric());
    }

    public function testIsHistoric90Days()
    {
        $dtItemDate = new \DateTime("now -90 days");
        $feedItemReview = $this->generateModel($dtItemDate);
        $this->assertEquals(true, $feedItemReview->isHistoric());
    }

}
