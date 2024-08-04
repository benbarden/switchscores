<?php

namespace Tests\Unit\Domain\Scraper;

use App\Domain\Scraper\NintendoCoUkPackshot;
use Tests\TestCase;

class ScraperImageTest extends TestCase
{
    /**
     * @var NintendoCoUkPackshot
     */
    private $scraper;

    public function setUp(): void
    {
        $this->scraper = new NintendoCoUkPackshot();

        parent::setUp();
    }

    public function tearDown(): void
    {
        unset($this->scraper);

        parent::tearDown();
    }

    public function testHeaderUrlUndernauts()
    {
        $this->scraper->crawlPage('https://www.nintendo.co.uk/Games/Nintendo-Switch-games/Undernauts-Labyrinth-of-Yomi-2173360.html');
        $ogImage = $this->scraper->getHeaderUrl();
        $expected = 'https://www.nintendo.com/eu/media/images/10_share_images/games_15/nintendo_switch_4/H2x1_NSwitch_UndernautsLabyrinthOfYomi_image1280w.jpg';

        $this->assertEquals($expected, $ogImage);
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
