<?php

namespace App\Services\Eshop;

use App\EshopEuropeGame;
use App\Game;

class PackshotEurope
{
    private $destFilename;

    private $remoteUrl;

    private $remoteFileExt;

    private $publicImagePath;

    private $isAborted;

    public function setRemoteUrl($remoteUrl)
    {
        $this->remoteUrl = $remoteUrl;
    }

    public function setRemoteFileExt($fileExt)
    {
        $this->remoteFileExt = $fileExt;
    }

    public function setPublicImagePath($imagePath)
    {
        $this->publicImagePath = $imagePath;
    }

    public function generateRemoteUrl(EshopEuropeGame $eshopEuropeGame, $mode)
    {
        if ($mode == 'square') {
            $imageUrl = $eshopEuropeGame->image_url_sq_s;
        } elseif ($mode == 'header') {
            $imageUrl = $eshopEuropeGame->image_url_h2x1_s;
        } else {
            throw new \Exception('Unknown mode: '.$mode);
        }
        $fileExt = pathinfo($imageUrl, PATHINFO_EXTENSION);
        $this->setRemoteUrl($imageUrl);
        $this->setRemoteFileExt($fileExt);
    }

    public function removeExistingFile($abortIfFileExists = true)
    {
        // Remove any existing file with this name
        if (file_exists($this->publicImagePath.$this->destFilename)) {
            if ($abortIfFileExists) {
                // If the file exists, we don't need to download it
                $this->isAborted = true;
                return false;
            } else {
                // Remove existing file if we're forcing all files to re-download
                unlink($this->publicImagePath.$this->destFilename);
            }
        }
    }

    public function downloadFile()
    {
        $storagePath = storage_path().'/tmp/';

        // Save the file
        $imageData = file_get_contents('https:'.$this->remoteUrl);
        file_put_contents($storagePath.$this->destFilename, $imageData);

        // Move it to the right place
        rename($storagePath.$this->destFilename, $this->publicImagePath.$this->destFilename);
    }

    public function downloadSquarePackshot(EshopEuropeGame $eshopEuropeGame, Game $game, $abortIfFileExists = true)
    {
        $this->isAborted = false;
        $gameLinkTitle = $game->link_title;

        $this->generateRemoteUrl($eshopEuropeGame, 'square');

        // This stops redownloading all the files that weren't named as sq-xxxxx.jpg
        $gameExistingPackshotFile = $game->boxart_square_url;
        if ($gameExistingPackshotFile) {
            $this->destFilename = $gameExistingPackshotFile;
        } else {
            $this->destFilename = 'sq-'.$gameLinkTitle.'.'.$this->remoteFileExt;
        }

        $this->publicImagePath = public_path().'/img/games/square/';

        $this->removeExistingFile();
        if ($this->isAborted) return false;

        $this->downloadFile();
    }

    public function downloadHeaderImage(EshopEuropeGame $eshopEuropeGame, Game $game, $abortIfFileExists = true)
    {
        $this->isAborted = false;
        $gameLinkTitle = $game->link_title;

        $this->generateRemoteUrl($eshopEuropeGame, 'header');

        $gameExistingPackshotFile = $game->boxart_header_image;
        if ($gameExistingPackshotFile) {
            $this->destFilename = $gameExistingPackshotFile;
        } else {
            $this->destFilename = $gameLinkTitle.'.'.$this->remoteFileExt;
        }

        $this->publicImagePath = public_path().'/img/games/header/';

        $this->removeExistingFile();
        if ($this->isAborted) return false;

        $this->downloadFile();
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