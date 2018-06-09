<?php

namespace Tests\Unit\Services\Review;

use Illuminate\Support\Collection;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\Services\ReviewStatsService;
use App\ReviewLink;

class ReviewStatsServiceTest extends TestCase
{
    public function testReviewCountThree()
    {
        $reviews = new Collection();
        $reviewLink = new ReviewLink();
        $reviews->push($reviewLink)->push($reviewLink)->push($reviewLink);

        $serviceStats = new ReviewStatsService;
        $reviewCount = $serviceStats->calculateReviewCount($reviews);

        $this->assertEquals(3, $reviewCount);
    }

    public function testReviewAverageThreeItems()
    {
        $reviews = new Collection();

        $reviewLinkA = new ReviewLink();
        $reviewLinkA->rating_normalised = 7.0;

        $reviewLinkB = new ReviewLink();
        $reviewLinkB->rating_normalised = 8.0;

        $reviewLinkC = new ReviewLink();
        $reviewLinkC->rating_normalised = 9.0;

        $reviews
            ->push($reviewLinkA)
            ->push($reviewLinkB)
            ->push($reviewLinkC);

        $serviceStats = new ReviewStatsService;
        $reviewAvg = $serviceStats->calculateReviewAverage($reviews);

        $this->assertEquals(8.0, $reviewAvg);
    }

    public function testReviewAverageFourItems()
    {
        $reviews = new Collection();

        $reviewLinkA = new ReviewLink();
        $reviewLinkA->rating_normalised = 7.0;

        $reviewLinkB = new ReviewLink();
        $reviewLinkB->rating_normalised = 8.0;

        $reviewLinkC = new ReviewLink();
        $reviewLinkC->rating_normalised = 9.0;

        $reviewLinkD = new ReviewLink();
        $reviewLinkD->rating_normalised = 10.0;

        $reviews
            ->push($reviewLinkA)
            ->push($reviewLinkB)
            ->push($reviewLinkC)
            ->push($reviewLinkD);

        $serviceStats = new ReviewStatsService;
        $reviewAvg = $serviceStats->calculateReviewAverage($reviews);

        $this->assertEquals(8.5, $reviewAvg);
    }

    public function testReviewAverageFiveItems()
    {
        $reviews = new Collection();

        $reviewLinkA = new ReviewLink();
        $reviewLinkA->rating_normalised = 8.5;

        $reviewLinkB = new ReviewLink();
        $reviewLinkB->rating_normalised = 9.0;

        $reviewLinkC = new ReviewLink();
        $reviewLinkC->rating_normalised = 8.4;

        $reviewLinkD = new ReviewLink();
        $reviewLinkD->rating_normalised = 1.0;

        $reviewLinkE = new ReviewLink();
        $reviewLinkE->rating_normalised = 7.2;

        $reviews
            ->push($reviewLinkA)
            ->push($reviewLinkB)
            ->push($reviewLinkC)
            ->push($reviewLinkD)
            ->push($reviewLinkE);

        $serviceStats = new ReviewStatsService;
        $reviewAvg = $serviceStats->calculateReviewAverage($reviews);

        $this->assertEquals(6.82, $reviewAvg);
    }

    public function testReviewAverageTwoDecimalPlaces()
    {
        $reviews = new Collection();

        $reviewLinkA = new ReviewLink();
        $reviewLinkA->rating_normalised = 6.0;

        $reviewLinkB = new ReviewLink();
        $reviewLinkB->rating_normalised = 6.5;

        $reviews
            ->push($reviewLinkA)
            ->push($reviewLinkB);

        $serviceStats = new ReviewStatsService;
        $reviewAvg = $serviceStats->calculateReviewAverage($reviews);

        $this->assertEquals(6.25, $reviewAvg);
    }

    public function testStandardDeviationMarioKartDeluxe()
    {
        $reviews = new Collection();

        $reviewLinkItem = new ReviewLink();
        $reviewLinkItem->rating_normalised = 9.0;
        $reviews->push($reviewLinkItem);
        $reviewLinkItem = new ReviewLink();
        $reviewLinkItem->rating_normalised = 9.6;
        $reviews->push($reviewLinkItem);
        $reviewLinkItem = new ReviewLink();
        $reviewLinkItem->rating_normalised = 9.0;
        $reviews->push($reviewLinkItem);
        $reviewLinkItem = new ReviewLink();
        $reviewLinkItem->rating_normalised = 9.0;
        $reviews->push($reviewLinkItem);
        $reviewLinkItem = new ReviewLink();
        $reviewLinkItem->rating_normalised = 9.5;
        $reviews->push($reviewLinkItem);
        $reviewLinkItem = new ReviewLink();
        $reviewLinkItem->rating_normalised = 9.0;
        $reviews->push($reviewLinkItem);
        $reviewLinkItem = new ReviewLink();
        $reviewLinkItem->rating_normalised = 9.0;
        $reviews->push($reviewLinkItem);
        $reviewLinkItem = new ReviewLink();
        $reviewLinkItem->rating_normalised = 10.0;
        $reviews->push($reviewLinkItem);
        $reviewLinkItem = new ReviewLink();
        $reviewLinkItem->rating_normalised = 9.3;
        $reviews->push($reviewLinkItem);
        $reviewLinkItem = new ReviewLink();
        $reviewLinkItem->rating_normalised = 10.0;
        $reviews->push($reviewLinkItem);
        $reviewLinkItem = new ReviewLink();
        $reviewLinkItem->rating_normalised = 10.0;
        $reviews->push($reviewLinkItem);
        $reviewLinkItem = new ReviewLink();
        $reviewLinkItem->rating_normalised = 8.5;
        $reviews->push($reviewLinkItem);

        $serviceStats = new ReviewStatsService;

        $reviewAvg = $serviceStats->calculateReviewAverage($reviews);
        $this->assertEquals(9.33, $reviewAvg);

        $standardDeviation = $serviceStats->calculateStandardDeviation($reviews);
        $this->assertEquals(0.4938, $standardDeviation);
    }

    public function testStandardDeviationBrawl()
    {
        $reviews = new Collection();

        // Testing a broader range of scores but with only 4 reviews

        $reviewLinkItem = new ReviewLink();
        $reviewLinkItem->rating_normalised = 4.0;
        $reviews->push($reviewLinkItem);
        $reviewLinkItem = new ReviewLink();
        $reviewLinkItem->rating_normalised = 8.0;
        $reviews->push($reviewLinkItem);
        $reviewLinkItem = new ReviewLink();
        $reviewLinkItem->rating_normalised = 5.9;
        $reviews->push($reviewLinkItem);
        $reviewLinkItem = new ReviewLink();
        $reviewLinkItem->rating_normalised = 6.0;
        $reviews->push($reviewLinkItem);

        $serviceStats = new ReviewStatsService;

        $reviewAvg = $serviceStats->calculateReviewAverage($reviews);
        $this->assertEquals(5.98, $reviewAvg);

        $standardDeviation = $serviceStats->calculateStandardDeviation($reviews);
        $this->assertEquals(1.6338, $standardDeviation);
    }

    public function testStandardDeviationLevelsPlus()
    {
        $reviews = new Collection();

        // Big range (1 to 8)

        $reviewLinkItem = new ReviewLink();
        $reviewLinkItem->rating_normalised = 1.0;
        $reviews->push($reviewLinkItem);
        $reviewLinkItem = new ReviewLink();
        $reviewLinkItem->rating_normalised = 6.6;
        $reviews->push($reviewLinkItem);
        $reviewLinkItem = new ReviewLink();
        $reviewLinkItem->rating_normalised = 7.0;
        $reviews->push($reviewLinkItem);
        $reviewLinkItem = new ReviewLink();
        $reviewLinkItem->rating_normalised = 5.0;
        $reviews->push($reviewLinkItem);
        $reviewLinkItem = new ReviewLink();
        $reviewLinkItem->rating_normalised = 8.0;
        $reviews->push($reviewLinkItem);
        $reviewLinkItem = new ReviewLink();
        $reviewLinkItem->rating_normalised = 7.0;
        $reviews->push($reviewLinkItem);
        $reviewLinkItem = new ReviewLink();
        $reviewLinkItem->rating_normalised = 6.0;
        $reviews->push($reviewLinkItem);
        $reviewLinkItem = new ReviewLink();
        $reviewLinkItem->rating_normalised = 6.0;
        $reviews->push($reviewLinkItem);

        $serviceStats = new ReviewStatsService;

        $reviewAvg = $serviceStats->calculateReviewAverage($reviews);
        $this->assertEquals(5.83, $reviewAvg);

        $standardDeviation = $serviceStats->calculateStandardDeviation($reviews);
        $this->assertEquals(2.1419, $standardDeviation);
    }
}
