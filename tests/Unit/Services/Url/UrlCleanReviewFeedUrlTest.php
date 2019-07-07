<?php

namespace Tests\Unit\Services\Game;

use Tests\TestCase;
use Illuminate\Support\Collection;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\Services\UrlService;

class UrlCleanReviewFeedUrlTest extends TestCase
{
    /**
     * @var UrlService
     */
    private $urlService;

    public function setUp()
    {
        parent::setUp();
        $this->urlService = new UrlService();
    }

    public function tearDown()
    {
        parent::tearDown();
        unset($this->urlService);
    }

    public function testSimpleLink1()
    {
        $url = 'http://www.cubed3.com/review/5423/1/sisters-royale-nintendo-switch.html';
        $expected = $url;

        $cleanUrl = $this->urlService->cleanReviewFeedUrl($url);
        $this->assertEquals($expected, $cleanUrl);
    }

    public function testSimpleLink2()
    {
        $url = 'http://www.nintendolife.com/reviews/nintendo-switch/girls_und_panzer_dream_tank_match_dx_switch-eshop';
        $expected = $url;

        $cleanUrl = $this->urlService->cleanReviewFeedUrl($url);
        $this->assertEquals($expected, $cleanUrl);
    }

    public function testUtmVariables()
    {
        $url = 'https://twobeardgaming.com/2019/06/26/warlocks-2-god-slayers-nintendo-switch-review/?utm_source=rss&utm_medium=rss&utm_campaign=warlocks-2-god-slayers-nintendo-switch-review';
        $expected = 'https://twobeardgaming.com/2019/06/26/warlocks-2-god-slayers-nintendo-switch-review/';

        $cleanUrl = $this->urlService->cleanReviewFeedUrl($url);
        $this->assertEquals($expected, $cleanUrl);
    }
}
