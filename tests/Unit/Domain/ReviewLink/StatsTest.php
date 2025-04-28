<?php

namespace Tests\Unit\Domain\ReviewLink;

use App\Domain\ReviewLink\Stats;
use App\Models\ReviewLink;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

class StatsTest extends TestCase
{
    public function testNoReviews()
    {
        $reviewLinkStats = new Stats();

        $reviewLinks = new Collection();
        $quickReviews = new Collection();

        $result = $reviewLinkStats->calculateStats($reviewLinks, $quickReviews);

        $this->assertEquals(0, $result[0]);
        $this->assertEquals(0, $result[1]);
    }

    public function testReviewLinkSimple()
    {
        $reviewLinkStats = new Stats();

        $reviewLinks = new Collection();
        $quickReviews = new Collection();

        $linkItem1 = new ReviewLink(['rating_normalised' => 5]);
        $linkItem2 = new ReviewLink(['rating_normalised' => 5]);
        $reviewLinks->push($linkItem1);
        $reviewLinks->push($linkItem2);

        $result = $reviewLinkStats->calculateStats($reviewLinks, $quickReviews);

        $this->assertEquals(2, $result[0]);
        $this->assertEquals(5, $result[1]);
    }

    public function testReviewCountThree()
    {
        $reviewLinkStats = new Stats();

        $reviewLinks = new Collection();
        $quickReviews = new Collection();

        $linkItem1 = new ReviewLink();
        $linkItem2 = new ReviewLink();
        $linkItem3 = new ReviewLink();

        $reviewLinks->push($linkItem1)->push($linkItem2)->push($linkItem3);

        $result = $reviewLinkStats->calculateStats($reviewLinks, $quickReviews);

        $this->assertEquals(3, $result[0]);
        //$this->assertEquals(5, $result[1]);
    }

    public function testReviewAverageThreeItems()
    {
        $reviewLinkStats = new Stats();

        $reviewLinks = new Collection();
        $quickReviews = new Collection();

        $reviewLinkA = new ReviewLink();
        $reviewLinkA->rating_normalised = 7.0;

        $reviewLinkB = new ReviewLink();
        $reviewLinkB->rating_normalised = 8.0;

        $reviewLinkC = new ReviewLink();
        $reviewLinkC->rating_normalised = 9.0;

        $reviewLinks
            ->push($reviewLinkA)
            ->push($reviewLinkB)
            ->push($reviewLinkC);

        $result = $reviewLinkStats->calculateStats($reviewLinks, $quickReviews);

        $this->assertEquals(3, $result[0]);
        $this->assertEquals(8.0, $result[1]);
    }

    public function testReviewAverageFourItems()
    {
        $reviewLinkStats = new Stats();

        $reviewLinks = new Collection();
        $quickReviews = new Collection();

        $reviewLinkA = new ReviewLink();
        $reviewLinkA->rating_normalised = 7.0;

        $reviewLinkB = new ReviewLink();
        $reviewLinkB->rating_normalised = 8.0;

        $reviewLinkC = new ReviewLink();
        $reviewLinkC->rating_normalised = 9.0;

        $reviewLinkD = new ReviewLink();
        $reviewLinkD->rating_normalised = 10.0;

        $reviewLinks
            ->push($reviewLinkA)
            ->push($reviewLinkB)
            ->push($reviewLinkC)
            ->push($reviewLinkD);

        $result = $reviewLinkStats->calculateStats($reviewLinks, $quickReviews);

        $this->assertEquals(4, $result[0]);
        $this->assertEquals(8.5, $result[1]);
    }

    public function testReviewAverageFiveItems()
    {
        $reviewLinkStats = new Stats();

        $reviewLinks = new Collection();
        $quickReviews = new Collection();

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

        $reviewLinks
            ->push($reviewLinkA)
            ->push($reviewLinkB)
            ->push($reviewLinkC)
            ->push($reviewLinkD)
            ->push($reviewLinkE);

        $result = $reviewLinkStats->calculateStats($reviewLinks, $quickReviews);

        $this->assertEquals(5, $result[0]);
        $this->assertEquals(6.82, $result[1]);
    }

    public function testReviewAverageTwoDecimalPlaces()
    {
        $reviewLinkStats = new Stats();

        $reviewLinks = new Collection();
        $quickReviews = new Collection();

        $reviewLinkA = new ReviewLink();
        $reviewLinkA->rating_normalised = 6.0;

        $reviewLinkB = new ReviewLink();
        $reviewLinkB->rating_normalised = 6.5;

        $reviewLinks
            ->push($reviewLinkA)
            ->push($reviewLinkB);

        $result = $reviewLinkStats->calculateStats($reviewLinks, $quickReviews);

        $this->assertEquals(2, $result[0]);
        $this->assertEquals(6.25, $result[1]);
    }

}
