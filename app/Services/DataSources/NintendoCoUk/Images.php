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
        if ($remoteFile) {
            $destPath = public_path().GameImages::PATH_IMAGE_SQUARE;
            $destFilename = $this->generateDestFilename($remoteFile, 'sq-');
            if (!file_exists($destPath . $destFilename)) {
                $isDownloaded = $this->downloadFile($remoteFile, $destPath, $destFilename);
            } else {
                // Already downloaded
                $isDownloaded = true;
            }
            if ($isDownloaded) {
                $this->squareFilename = $destFilename;
                $this->squareDownloaded = true;
            }
        }
    }

    public function downloadHeader()
    {
        $remoteFile = $this->dsParsedItem->image_header;
        if ($remoteFile) {
            $destPath = public_path().GameImages::PATH_IMAGE_HEADER;
            $destFilename = $this->generateDestFilename($remoteFile, 'hdr-');
            if (!file_exists($destPath.$destFilename)) {
                $isDownloaded = $this->downloadFile($remoteFile, $destPath, $destFilename);
            } else {
                // Already downloaded
                $isDownloaded = true;
            }
            if ($isDownloaded) {
                $this->headerFilename = $destFilename;
                $this->headerDownloaded = true;
            }
        }
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
        if (!$remoteUrl) {
            throw new \Exception('Remote URL cannot be blank');
        }

        $storagePath = storage_path().self::PATH_TMP;

        try {

            // Add protocol if needed
            $protocolMatch = 'https:';
            if (substr($remoteUrl, 0, strlen($protocolMatch)) != $protocolMatch) {
                $remoteUrl = $protocolMatch.$remoteUrl;
            }

            // Save the file
            $imageData = file_get_contents($remoteUrl);
            file_put_contents($storagePath.$destFilename, $imageData);

            // Move it to the right place
            rename($storagePath.$destFilename, $destPath.$destFilename);

        } catch (\ErrorException $e) {
            throw new \Exception('Error saving file: '.$e->getMessage());
            return false;
        }

        // Success!
        return true;
    }
}
