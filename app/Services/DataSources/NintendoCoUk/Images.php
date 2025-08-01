<?php

namespace App\Services\DataSources\NintendoCoUk;

use App\Models\DataSourceParsed;
use App\Models\Game;
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

    public function __construct(Game $game)
    {
        $this->game = $game;

        $this->squareDownloaded = false;
        $this->headerDownloaded = false;
        $this->squareFilename = null;
        $this->headerFilename = null;
    }

    public function setDSParsedItem(DataSourceParsed $dsParsedItem)
    {
        $this->dsParsedItem = $dsParsedItem;
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

    public function downloadRemoteHeader($imageUrl, $gameId)
    {
        $destPath = public_path().GameImages::PATH_IMAGE_HEADER;
        $prefix = 'hdr-';

        $destFilename = $this->generateDestFilename($imageUrl, $prefix, $gameId);
        return $this->downloadRemote($imageUrl, $destPath, $destFilename);
    }

    public function downloadRemoteSquare($imageUrl, $gameId)
    {
        $destPath = public_path().GameImages::PATH_IMAGE_SQUARE;
        $prefix = 'sq-';

        $destFilename = $this->generateDestFilename($imageUrl, $prefix, $gameId);
        return $this->downloadRemote($imageUrl, $destPath, $destFilename);
    }

    public function downloadRemote($imageUrl, $destPath, $destFilename)
    {
        if (!file_exists($destPath.$destFilename)) {
            $isDownloaded = $this->downloadFile($imageUrl, $destPath, $destFilename);
        } else {
            // Already downloaded
            $isDownloaded = true;
        }

        if (!$isDownloaded) {
            throw new \Exception('Could not download file: ' . $imageUrl);
        }

        return $destFilename;
    }

    /**
     * This is for packshots that exist on the file system, but may be using a custom name format.
     * If we force a check against the expected format, custom images will never pass the check.
     * @return string
     */
    public function generateCurrentPathHeader(Game $game)
    {
        $destPath = public_path().GameImages::PATH_IMAGE_HEADER;
        $fullPath = $destPath.$game->image_header;
        return $fullPath;
    }

    /**
     * This is for packshots that exist on the file system, but may be using a custom name format.
     * If we force a check against the expected format, custom images will never pass the check.
     * @return string
     */
    public function generateCurrentPathSquare(Game $game)
    {
        $destPath = public_path().GameImages::PATH_IMAGE_SQUARE;
        $fullPath = $destPath.$game->image_square;
        return $fullPath;
    }

    public function generateFullDestPathHeader($imageUrl, $suffixId = null)
    {
        if ($suffixId == null) $suffixId = $this->game->id;
        $destPath = public_path().GameImages::PATH_IMAGE_HEADER;
        $prefix = 'hdr-';
        $destFilename = $this->generateDestFilename($imageUrl, $prefix, $suffixId);
        $fullPath = $destPath.$destFilename;
        return $fullPath;
    }

    public function generateFullDestPathSquare($imageUrl, $suffixId = null)
    {
        if ($suffixId == null) $suffixId = $this->game->id;
        $destPath = public_path().GameImages::PATH_IMAGE_SQUARE;
        $prefix = 'sq-';
        $destFilename = $this->generateDestFilename($imageUrl, $prefix, $suffixId);
        $fullPath = $destPath.$destFilename;
        return $fullPath;
    }

    public function generateDestFilename($remoteFile, $prefix = '', $suffixId = '')
    {
        if ($suffixId) {
            $prefix .= $suffixId.'-';
        } else {
            $linkId = $this->dsParsedItem->link_id;
            if ($linkId) {
                $prefix .= $linkId.'-';
            }
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
            $origRemoteUrl = $remoteUrl;
            if (!str_starts_with($remoteUrl, $protocolMatch)) {
                $remoteUrl = $protocolMatch.$remoteUrl;
            }

            // Save the file
            $imageData = file_get_contents($remoteUrl);
            file_put_contents($storagePath.$destFilename, $imageData);

            // Move it to the right place
            rename($storagePath.$destFilename, $destPath.$destFilename);

        } catch (\ErrorException $e) {
            $errorData = [
                'origRemoteUrl' => $origRemoteUrl,
                'remoteUrl' => $remoteUrl,
                'storagePath' => $storagePath,
                'destPath' => $destPath,
                'destFilename' => $destFilename,
                'moveFrom' => $storagePath.$destFilename,
                'moveTo' => $destPath.$destFilename,
            ];
            throw new \Exception('Error saving file: '.$e->getMessage().'; Error data: '.var_export($errorData, true));
        }

        // Success!
        return true;
    }
}
