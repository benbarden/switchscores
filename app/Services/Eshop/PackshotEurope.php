<?php

namespace App\Services\Eshop;

use App\EshopEuropeGame;
use App\Game;

class PackshotEurope
{
    private $destFilename;

    private $isAborted;

    public function downloadPackshot(EshopEuropeGame $eshopEuropeGame, Game $game, $abortIfFileExists = true)
    {
        $this->isAborted = false;
        $gameLinkTitle = $game->link_title;

        $imageUrlSquare = $eshopEuropeGame->image_url_sq_s;
        $fileExt = pathinfo($imageUrlSquare, PATHINFO_EXTENSION);

        // This stops redownloading all the files that weren't named as sq-xxxxx.jpg
        $gameExistingPackshotFile = $game->boxart_square_url;
        if ($gameExistingPackshotFile) {
            $destFilename = $gameExistingPackshotFile;
        } else {
            $destFilename = 'sq-'.$gameLinkTitle.'.'.$fileExt;
        }

        $storagePath = storage_path().'/tmp/';
        $publicImagePath = public_path().'/img/games/square/';

        $this->destFilename = $destFilename;

        // Remove any existing file with this name
        if (file_exists($publicImagePath.$destFilename)) {
            if ($abortIfFileExists) {
                // If the file exists, we don't need to download it
                $this->isAborted = true;
                return false;
            } else {
                // Remove existing file if we're forcing all files to re-download
                unlink($publicImagePath.$destFilename);
            }
        }

        // Save the file
        $imageData = file_get_contents('https:'.$imageUrlSquare);
        file_put_contents($storagePath.$destFilename, $imageData);

        // Move it to the right place
        rename($storagePath.$destFilename, $publicImagePath.$destFilename);
    }

    public function getDestFilename()
    {
        return $this->destFilename;
    }

    public function getIsAborted()
    {
        return $this->isAborted;
    }
}