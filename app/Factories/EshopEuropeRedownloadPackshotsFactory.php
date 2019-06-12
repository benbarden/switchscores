<?php

namespace App\Factories;

use App\Game;
use App\Services\Eshop\PackshotEurope;
use App\Services\EshopEuropeGameService;

class EshopEuropeRedownloadPackshotsFactory
{
    public static function redownloadPackshots(Game $game)
    {
        $servicePackshotEurope = new PackshotEurope();
        $serviceEshopEuropeGame = new EshopEuropeGameService();

        // GAME CORE DATA
        $gameId = $game->id;

        // Check if we have an eShop record linked to this game
        $fsId = $game->eshop_europe_fs_id;
        if (!$fsId) {
            throw new \Exception('No eShop record linked to game: '.$gameId);
        }
        $eshopItem = $serviceEshopEuropeGame->getByFsId($fsId);
        if (!$eshopItem) {
            throw new \Exception('Cannot locate eShop record linked to game: '.$gameId);
        }

        // Square
        $servicePackshotEurope->downloadSquarePackshot($eshopItem, $game);
        $destFilename = $servicePackshotEurope->getDestFilename();
        if ($servicePackshotEurope->getIsAborted() == false) {
            $game->boxart_square_url = $destFilename;
            $game->save();
        }

        // Header
        $servicePackshotEurope->downloadHeaderImage($eshopItem, $game);
        $destFilename = $servicePackshotEurope->getDestFilename();
        if ($servicePackshotEurope->getIsAborted() == false) {
            $game->boxart_header_image = $destFilename;
            $game->save();
        }
    }
}