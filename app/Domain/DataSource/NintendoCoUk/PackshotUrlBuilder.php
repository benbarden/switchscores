<?php

namespace App\Domain\DataSource\NintendoCoUk;

class PackshotUrlBuilder
{
    const CDN_URL_BASE = 'https://fs-prod-cdn.nintendo-europe.com/media/images/';

    public function getSquareUrl($headerUrl)
    {
        preg_match('/.*\/2x1_(NSwitch|NSwitchDS)?_([\w_]+)_image(1600|1280)w\.jpg/', $headerUrl, $packshotMatch);
        if (!$packshotMatch) {
            throw new \Exception('Cannot find square URL match from header URL: '.$headerUrl);
        }

        $imageType = $packshotMatch[1];
        $imageName = $packshotMatch[2];
        $imageSize = $packshotMatch[3];

        if ($imageType == "NSwitch") {
            $squareUrl = self::CDN_URL_BASE."11_square_images/games_18/nintendo_switch_5/1x1_NSwitch_".$imageName."_image500w.jpg";
        } else {
            $squareUrl = self::CDN_URL_BASE."11_square_images/games_18/nintendo_switch_download_software/1x1_NSwitchDS_".$imageName."_image500w.jpg";
        }

        return $squareUrl;
    }
}