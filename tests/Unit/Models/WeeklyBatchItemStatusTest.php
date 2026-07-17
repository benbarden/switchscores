<?php

namespace Tests\Unit\Models;

use App\Models\WeeklyBatchItem;
use Tests\TestCase;

class WeeklyBatchItemStatusTest extends TestCase
{
    // ---- post-fetch status decision ----

    public function testConfirmedLqAlwaysBecomesLowQuality()
    {
        // Even with a packshot already present, a confirmed-LQ publisher is auto LQ.
        $this->assertEquals(
            WeeklyBatchItem::STATUS_LOW_QUALITY,
            WeeklyBatchItem::postFetchStatus(true, true, true, true)
        );
    }

    public function testLqFlagGoesToReviewEvenWhenPackshotPresent()
    {
        // Regression: HTML paste pre-fills packshot_url, which must NOT suppress LQ review.
        $this->assertEquals(
            WeeklyBatchItem::STATUS_LQ_REVIEW,
            WeeklyBatchItem::postFetchStatus(false, true, true, false)
        );
    }

    public function testLqFlagGoesToReviewWithoutPackshot()
    {
        $this->assertEquals(
            WeeklyBatchItem::STATUS_LQ_REVIEW,
            WeeklyBatchItem::postFetchStatus(false, true, false, false)
        );
    }

    public function testCleanItemWithPackshotSkipsToCategory()
    {
        // The HTML-paste win: packshot already known, so skip the packshot stage.
        $this->assertEquals(
            WeeklyBatchItem::STATUS_CATEGORY_PENDING,
            WeeklyBatchItem::postFetchStatus(false, false, true, false)
        );
    }

    public function testCleanItemWithPackshotAndCategoryIsReady()
    {
        $this->assertEquals(
            WeeklyBatchItem::STATUS_READY,
            WeeklyBatchItem::postFetchStatus(false, false, true, true)
        );
    }

    public function testCleanItemWithoutPackshotNeedsPackshot()
    {
        // Plain-text flow: no packshot captured, so the packshot stage still applies.
        $this->assertEquals(
            WeeklyBatchItem::STATUS_PACKSHOT_PENDING,
            WeeklyBatchItem::postFetchStatus(false, false, false, false)
        );
    }
}
