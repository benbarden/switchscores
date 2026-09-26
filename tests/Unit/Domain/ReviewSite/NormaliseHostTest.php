<?php

namespace Tests\Unit\Domain\ReviewSite;

use App\Domain\ReviewSite\Repository as ReviewSiteRepository;

use Tests\TestCase;

/**
 * The host comparison behind suggesting an existing review site on the feed onboarding
 * screen. Stored website URLs vary in scheme, www and trailing path, and a feed URL carries
 * a path and query string, so all of these need to compare equal.
 */
class NormaliseHostTest extends TestCase
{
    public function testSchemeAndWwwAreIgnored()
    {
        $this->assertEquals('catwithmonocle.com', ReviewSiteRepository::normaliseHost('https://www.catwithmonocle.com/'));
        $this->assertEquals('catwithmonocle.com', ReviewSiteRepository::normaliseHost('http://catwithmonocle.com'));
    }

    public function testAFeedUrlReducesToItsHost()
    {
        $this->assertEquals(
            'catwithmonocle.com',
            ReviewSiteRepository::normaliseHost('https://catwithmonocle.com/feed/?category_name=reviews%20switch-2')
        );
    }

    public function testCaseIsIgnored()
    {
        $this->assertEquals('example.com', ReviewSiteRepository::normaliseHost('https://Example.COM/'));
    }

    public function testAUrlWithNoSchemeIsStillReadable()
    {
        $this->assertEquals('example.com', ReviewSiteRepository::normaliseHost('www.example.com/reviews'));
    }

    public function testASubdomainOtherThanWwwIsKept()
    {
        $this->assertEquals('reviews.example.com', ReviewSiteRepository::normaliseHost('https://reviews.example.com/'));
    }

    public function testEmptyInputGivesNull()
    {
        $this->assertNull(ReviewSiteRepository::normaliseHost(''));
        $this->assertNull(ReviewSiteRepository::normaliseHost(null));
    }
}
