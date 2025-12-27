<?php

namespace Tests\Unit\Domain\Scraper;

use App\Domain\Scraper\Score;
use Tests\TestCase;

class ScoreTest extends TestCase
{
    /**
     * @var Score
     */
    private $scraper;

    public function setUp(): void
    {
        $this->scraper = new Score();

        parent::setUp();
    }

    public function tearDown(): void
    {
        unset($this->scraper);

        parent::tearDown();
    }

    public function testPureNintendoLuigisMansion2HD()
    {
        $this->scraper->crawlPage('https://purenintendo.com/review-luigis-mansion-2-hd-nintendo-switch/');
        $value = $this->scraper->spanItemPropRatingValueNoChildren();
        $expected = '8';

        $this->assertEquals($expected, $value);
    }

    public function testPS3BlogNetOverHorizonXSteelEmpire()
    {
        $this->scraper->crawlPage('https://www.ps3blog.net/2024/08/05/nintendo-switch-over-horizon-x-steel-empire-review/');
        $value = $this->scraper->spanItemPropRatingValueNoChildren();
        $expected = '8.5';

        $this->assertEquals($expected, $value);
    }

    public function testSwitchabooNintendoWorldChampionships()
    {
        $this->scraper->crawlPage('https://www.switchaboo.com/nintendo-world-championships-nes-edition-switch-review/');
        $value = $this->scraper->customSwitchaboo();
        $expected = '7';

        $this->assertEquals($expected, $value);
    }

    public function testPS4BlogNetSamAndMaxDevilsPlayhouse()
    {
        $this->scraper->crawlPage('https://www.ps4blog.net/2024/08/nintendo-switch-sam-and-max-the-devils-playhouse-remastered-review/');
        $value = $this->scraper->customPS4BlogNet();
        $expected = '9.0';

        $this->assertEquals($expected, $value);
    }

}
