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

    public function testNintenpediaEverafterFalls()
    {
        $this->scraper->crawlPage('https://nintenpedia.com/everafter-falls-review/');
        $value = $this->scraper->customNintenpedia();
        $expected = '6';

        $this->assertEquals($expected, $value);
    }

    public function testSwitchabooNintendoWorldChampionships()
    {
        $this->scraper->crawlPage('https://www.switchaboo.com/nintendo-world-championships-nes-edition-switch-review/');
        $value = $this->scraper->customSwitchaboo();
        $expected = '7';

        $this->assertEquals($expected, $value);
    }

    public function testHeyPoorPlayerHotLapRacing()
    {
        $this->scraper->crawlPage('https://www.heypoorplayer.com/2024/08/03/hot-lap-racing-review-switch/');
        $value = $this->scraper->customHeyPoorPlayer();
        $expected = '2.5';

        $this->assertEquals($expected, $value);
    }

    /**
     * Can't use this since the square images were removed from the pages.
    public function testSquareUrlUndernauts()
    {
        $this->scraper->crawlPage('https://www.nintendo.co.uk/Games/Nintendo-Switch-games/Undernauts-Labyrinth-of-Yomi-2173360.html');
        $ogImage = $this->scraper->getSquareUrl();
        $expected = 'https://fs-prod-cdn.nintendo-europe.com/media/images/05_packshots/games_13/nintendo_switch_8/PS_NSwitch_UndernautsLabyrinthOfYomi_PEGI.jpg';

        $this->assertEquals($expected, $ogImage);
    }
    */
}
