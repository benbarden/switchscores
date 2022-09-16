<?php

namespace Tests\Unit\Services\Review;

use App\Models\ReviewDraft;
use App\Models\ReviewSite;
use Carbon\Carbon;
use Tests\TestCase;

class ReviewDraftTest extends TestCase
{
    private function generateModel(\DateTime $dtItemDate)
    {
        $reviewDraft = new ReviewDraft;

        // Basic fields
        $reviewDraft->site_id = ReviewSite::SITE_SWITCH_SCORES;
        $reviewDraft->item_url = '/abc';
        $reviewDraft->item_title = 'Test Abc';

        // Date
        $pubDate = $dtItemDate->format('Y-m-d H:i:s');
        $pubDateModel = new Carbon($pubDate);
        $reviewDraft->item_date = $pubDateModel->format('Y-m-d H:i:s');

        return $reviewDraft;
    }

    public function testIsHistoricNow()
    {
        $dtItemDate = new \DateTime("now");
        $reviewDraft = $this->generateModel($dtItemDate);
        $this->assertEquals(false, $reviewDraft->isHistoric());
    }

    public function testIsHistoric7Days()
    {
        $dtItemDate = new \DateTime("now -7 days");
        $reviewDraft = $this->generateModel($dtItemDate);
        $this->assertEquals(false, $reviewDraft->isHistoric());
    }

    public function testIsHistoric14Days()
    {
        $dtItemDate = new \DateTime("now -14 days");
        $reviewDraft = $this->generateModel($dtItemDate);
        $this->assertEquals(false, $reviewDraft->isHistoric());
    }

    public function testIsHistoric21Days()
    {
        $dtItemDate = new \DateTime("now -21 days");
        $reviewDraft = $this->generateModel($dtItemDate);
        $this->assertEquals(false, $reviewDraft->isHistoric());
    }

    public function testIsHistoric28Days()
    {
        $dtItemDate = new \DateTime("now -28 days");
        $reviewDraft = $this->generateModel($dtItemDate);
        $this->assertEquals(false, $reviewDraft->isHistoric());
    }

    public function testIsHistoric30Days()
    {
        $dtItemDate = new \DateTime("now -30 days");
        $reviewDraft = $this->generateModel($dtItemDate);
        $this->assertEquals(false, $reviewDraft->isHistoric());
    }

    public function testIsHistoric31Days()
    {
        $dtItemDate = new \DateTime("now -31 days");
        $reviewDraft = $this->generateModel($dtItemDate);
        $this->assertEquals(true, $reviewDraft->isHistoric());
    }

    public function testIsHistoric90Days()
    {
        $dtItemDate = new \DateTime("now -90 days");
        $reviewDraft = $this->generateModel($dtItemDate);
        $this->assertEquals(true, $reviewDraft->isHistoric());
    }

}
