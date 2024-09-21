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

    public function testGodIsAGeekNoreyaTheGoldProject()
    {
        $this->scraper->crawlPage('https://www.godisageek.com/reviews/noreya-the-gold-project-review/');
        $value = $this->scraper->divItemPropRatingValueWithChildren();
        $expected = '6.5';

        $this->assertEquals($expected, $value);
    }

    public function testGodIsAGeekGestaltSteamCinder()
    {
        $this->scraper->crawlPage('https://www.godisageek.com/reviews/gestalt-steam-cinder-review/');
        $value = $this->scraper->divItemPropRatingValueWithChildren();
        $expected = '8.0';

        $this->assertEquals($expected, $value);
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

    public function testNintenpediaEverafterFalls()
    {
        $this->scraper->crawlPage('https://nintenpedia.com/everafter-falls-review/');
        $value = $this->scraper->customNintenpedia();
        $expected = '6';

        $this->assertEquals($expected, $value);
    }

    public function testNintenpediaEnderLilies()
    {
        $this->scraper->crawlPage('https://nintenpedia.com/ender-lilies-quietus-of-the-knights-review/');
        $value = $this->scraper->customNintenpedia();
        $expected = '7';

        $this->assertEquals($expected, $value);
    }

    public function testNintenpediaTurnipBoy()
    {
        $this->scraper->crawlPage('https://nintenpedia.com/turnip-boy-commits-tax-evasion-review/');
        $value = $this->scraper->customNintenpedia();
        $expected = '7';

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

    /**
     * Doesn't work.
    public function testHeyPoorPlayerHotLapRacing()
    {
        $this->scraper->crawlPage('https://www.heypoorplayer.com/2024/08/03/hot-lap-racing-review-switch/');
        $value = $this->scraper->customHeyPoorPlayer();
        $expected = '2.5';

        $this->assertEquals($expected, $value);
    }
     */

}
