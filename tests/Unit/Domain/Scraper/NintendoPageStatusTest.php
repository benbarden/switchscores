<?php

namespace Tests\Unit\Domain\Scraper;

use App\Domain\Scraper\NintendoPageStatus;
use Tests\TestCase;

/**
 * Nintendo answers a de-listed game with a redirect to its error page, which returns
 * HTTP 200 - so status alone says the fetch worked. The final URL is the real signal.
 *
 * Verified live on 2026-09-04: a de-listed game URL returned 200 after one redirect,
 * ending at https://www.nintendo.com/en-gb/404.html with the title "404 - I AM ERROR".
 */
class NintendoPageStatusTest extends TestCase
{
    public function testTheErrorPageUrlIsRecognised()
    {
        $this->assertTrue(NintendoPageStatus::isSoft404('https://www.nintendo.com/en-gb/404.html'));
    }

    public function testALiveGamePageIsNot()
    {
        $this->assertFalse(NintendoPageStatus::isSoft404(
            'https://www.nintendo.com/en-gb/Games/Nintendo-Switch-download-software/Cave-Looters-3142703.html'
        ));
    }

    public function testOtherRegionErrorPathsAreRecognised()
    {
        $this->assertTrue(NintendoPageStatus::isSoft404('https://www.nintendo.com/404'));
        $this->assertTrue(NintendoPageStatus::isSoft404('https://www.nintendo.com/en-gb/404'));
    }

    public function testAGameWhoseTitleContainsThoseDigitsIsNotAFalsePositive()
    {
        // Guards the bare '/404' pattern against matching a real game path
        $this->assertFalse(NintendoPageStatus::isSoft404(
            'https://www.nintendo.com/en-gb/Games/Nintendo-Switch-games/Game-404-Error-1234567.html'
        ));
    }
}
