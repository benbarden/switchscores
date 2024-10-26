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
}
