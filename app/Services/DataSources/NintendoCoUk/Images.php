<?php

namespace App\Services\DataSources\NintendoCoUk;

use App\Game;
use App\DataSourceParsed;
use App\Services\Game\Images as GameImages;

class Images
{
    const PATH_TMP = '/tmp/';

    /**
     * @var Game
     */
    private $game;

    /**
     * @var DataSourceParsed
     */
    private $dsParsedItem;

    /**
     * @var boolean
     */
    private $squareDownloaded;

    /**
     * @var boolean
     */
    private $headerDownloaded;

    /**
     * @var string
     */
    private $squareFilename;

    /**
     * @var string
     */
    private $headerFilename;

    public function __construct(Game $game, DataSourceParsed $dsParsedItem)
    {
        $this->game = $game;
        $this->dsParsedItem = $dsParsedItem;

        $this->squareDownloaded = false;
        $this->headerDownloaded = false;
        $this->squareFilename = null;
        $this->headerFilename = null;
    }

    public function squareDownloaded()
    {
        return $this->squareDownloaded;
    }

    public function headerDownloaded()
    {
        return $this->headerDownloaded;
    }

    public function getSquareFilename()
    {
        return $this->squareFilename;
    }

    public function getHeaderFilename()
    {
        return $this->headerFilename;
    }

    public function downloadSquare()
    {
        $remoteFile = $this->dsParsedItem->image_square;
        $destPath = public_path().GameImages::PATH_IMAGE_SQUARE;
        $destFilename = $this->generateDestFilename($remoteFile, 'sq-');
        if (!file_exists($destPath.$destFilename)) {
            $this->downloadFile($remoteFile, $destPath, $destFilename);
        }
        $this->squareFilename = $destFilename;
        $this->squareDownloaded = true;
    }

    public function downloadHeader()
    {
        $destPath = public_path().GameImages::PATH_IMAGE_HEADER;
        $remoteFile = $this->dsParsedItem->image_header;
        $destFilename = $this->generateDestFilename($remoteFile, 'hdr-');
        if (!file_exists($destPath.$destFilename)) {
            $this->downloadFile($remoteFile, $destPath, $destFilename);
        }
        $this->headerFilename = $destFilename;
        $this->headerDownloaded = true;
    }

    public function generateDestFilename($remoteFile, $prefix = '')
    {
        $linkId = $this->dsParsedItem->link_id;
        if ($linkId) {
            $prefix .= $linkId.'-';
        }
        $fileExt = pathinfo($remoteFile, PATHINFO_EXTENSION);
        $destFilename = $prefix.$this->game->link_title.'.'.$fileExt;
        return $destFilename;
    }

    public function downloadFile($remoteUrl, $destPath, $destFilename)
    {
        $storagePath = storage_path().self::PATH_TMP;

        // Save the file
        $imageData = file_get_contents('https:'.$remoteUrl);
        file_put_contents($storagePath.$destFilename, $imageData);

        // Move it to the right place
        rename($storagePath.$destFilename, $destPath.$destFilename);
    }
}
