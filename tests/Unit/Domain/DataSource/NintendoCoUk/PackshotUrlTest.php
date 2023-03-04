<?php

namespace Tests\Unit\Domain\DataSource\NintendoCoUk;

use App\Domain\DataSource\NintendoCoUk\PackshotUrlBuilder;
use Tests\TestCase;

class PackshotUrlTest extends TestCase
{
    public function testPowerWashSimulator()
    {
        $headerUrl = 'https://fs-prod-cdn.nintendo-europe.com/media/images/10_share_images/games_15/nintendo_switch_download_software_1/2x1_NSwitchDS_PowerWashSimulator_image1600w.jpg';
        $expectedUrl = 'https://fs-prod-cdn.nintendo-europe.com/media/images/11_square_images/games_18/nintendo_switch_download_software/1x1_NSwitchDS_PowerWashSimulator_image500w.jpg';

        $packshotBuilder = new PackshotUrlBuilder();
        $squareUrl = $packshotBuilder->getSquareUrl($headerUrl);

        $this->assertEquals($expectedUrl, $squareUrl);
    }

    public function testDigimonWorldNextOrder()
    {
        $headerUrl = 'https://fs-prod-cdn.nintendo-europe.com/media/images/10_share_images/games_15/nintendo_switch_4/2x1_NSwitch_DigimonWorldNextOrder_image1600w.jpg';
        $expectedUrl = 'https://fs-prod-cdn.nintendo-europe.com/media/images/11_square_images/games_18/nintendo_switch_5/1x1_NSwitch_DigimonWorldNextOrder_image500w.jpg';

        $packshotBuilder = new PackshotUrlBuilder();
        $squareUrl = $packshotBuilder->getSquareUrl($headerUrl);

        $this->assertEquals($expectedUrl, $squareUrl);
    }

    public function testPutridShotUltra()
    {
        $headerUrl = 'https://fs-prod-cdn.nintendo-europe.com/media/images/10_share_images/games_15/nintendo_switch_download_software_1/2x1_NSwitchDS_PutridShotUltra_image1600w.jpg';
        $expectedUrl = 'https://fs-prod-cdn.nintendo-europe.com/media/images/11_square_images/games_18/nintendo_switch_download_software/1x1_NSwitchDS_PutridShotUltra_image500w.jpg';

        $packshotBuilder = new PackshotUrlBuilder();
        $squareUrl = $packshotBuilder->getSquareUrl($headerUrl);

        $this->assertEquals($expectedUrl, $squareUrl);
    }

    public function testOctopathTravelerII()
    {
        $headerUrl = 'https://fs-prod-cdn.nintendo-europe.com/media/images/10_share_images/games_15/nintendo_switch_4/2x1_NSwitch_OctopathTravelerII_image1600w.jpg';
        $expectedUrl = 'https://fs-prod-cdn.nintendo-europe.com/media/images/11_square_images/games_18/nintendo_switch_5/1x1_NSwitch_OctopathTravelerII_image500w.jpg';

        $packshotBuilder = new PackshotUrlBuilder();
        $squareUrl = $packshotBuilder->getSquareUrl($headerUrl);

        $this->assertEquals($expectedUrl, $squareUrl);
    }

    public function testFurryFurySmashAndRoll()
    {
        $headerUrl = 'https://fs-prod-cdn.nintendo-europe.com/media/images/10_share_images/games_15/nintendo_switch_download_software_1/2x1_NSwitchDS_FurryFurySmashAndRoll_image1280w.jpg';
        $expectedUrl = 'https://fs-prod-cdn.nintendo-europe.com/media/images/11_square_images/games_18/nintendo_switch_download_software/1x1_NSwitchDS_FurryFurySmashAndRoll_image500w.jpg';

        $packshotBuilder = new PackshotUrlBuilder();
        $squareUrl = $packshotBuilder->getSquareUrl($headerUrl);

        $this->assertEquals($expectedUrl, $squareUrl);
    }

    public function testHoppingGirlKohane()
    {
        $headerUrl = 'https://fs-prod-cdn.nintendo-europe.com/media/images/10_share_images/games_15/nintendo_switch_download_software_1/2x1_NSwitchDS_HoppingGirlKohaneEx_image1600w.jpg';
        $expectedUrl = 'https://fs-prod-cdn.nintendo-europe.com/media/images/11_square_images/games_18/nintendo_switch_download_software/1x1_NSwitchDS_HoppingGirlKohaneEx_image500w.jpg';

        $packshotBuilder = new PackshotUrlBuilder();
        $squareUrl = $packshotBuilder->getSquareUrl($headerUrl);

        $this->assertEquals($expectedUrl, $squareUrl);
    }
}
