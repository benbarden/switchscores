<?php

namespace Tests\Unit\Services\Review;

use Illuminate\Support\Collection;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\Services\Review\StatsService;
use App\ReviewLink;

class StatsServiceTest extends TestCase
{
    public function testReviewCountThree()
    {
        $reviews = new Collection();
        $reviewLink = new ReviewLink();
        $reviews->push($reviewLink)->push($reviewLink)->push($reviewLink);

        $serviceStats = new StatsService;
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

        $serviceStats = new StatsService;
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

        $serviceStats = new StatsService;
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

        $serviceStats = new StatsService;
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

        $serviceStats = new StatsService;
        $reviewAvg = $serviceStats->calculateReviewAverage($reviews);

        $this->assertEquals(6.25, $reviewAvg);
    }

}
