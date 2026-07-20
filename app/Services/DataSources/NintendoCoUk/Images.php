<?php

namespace App\Services\DataSources\NintendoCoUk;

use App\Domain\Game\ImageResolver;
use App\Domain\Game\PackshotWriter;
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
            $destFilename = $this->generateDestFilename($remoteFile, 'sq-');
            $stored = $this->downloadAndStore($remoteFile, ImageResolver::TYPE_SQUARE, $destFilename);
            if ($stored) {
                $this->squareFilename = $stored;
                $this->squareDownloaded = true;
            }
        }
    }

    public function downloadHeader()
    {
        $remoteFile = $this->dsParsedItem->image_header;
        if ($remoteFile) {
            $destFilename = $this->generateDestFilename($remoteFile, 'hdr-');
            $stored = $this->downloadAndStore($remoteFile, ImageResolver::TYPE_HEADER, $destFilename);
            if ($stored) {
                $this->headerFilename = $stored;
                $this->headerDownloaded = true;
            }
        }
    }

    public function downloadRemoteHeader($imageUrl, $gameId)
    {
        $destFilename = $this->generateDestFilename($imageUrl, 'hdr-', $gameId);
        return $this->downloadAndStore($imageUrl, ImageResolver::TYPE_HEADER, $destFilename);
    }

    public function downloadRemoteSquare($imageUrl, $gameId)
    {
        $destFilename = $this->generateDestFilename($imageUrl, 'sq-', $gameId);
        return $this->downloadAndStore($imageUrl, ImageResolver::TYPE_SQUARE, $destFilename);
    }

    /**
     * Download a packshot to a temp file, then hand it to PackshotWriter to place.
     *
     * The service no longer decides where images live - the writer does, from
     * config('packshots.default_location'). It also owns persistence (the legacy column or
     * the game_images row), so callers must not set games.image_* themselves: under `spaces`
     * that would resurrect the legacy column and make the resolver serve a file that isn't
     * there.
     *
     * @return string the stored filename
     */
    private function downloadAndStore($imageUrl, string $type, string $legacyFilename)
    {
        $writer = app(PackshotWriter::class);

        // Legacy short-circuit, preserved: an identical file already on disk is not re-fetched.
        // Only meaningful for legacy, where the filename fully determines the path. Under
        // `spaces` the equivalent check is the eligibility test in DownloadPackshotHelper.
        if ($writer->defaultLocation() === PackshotWriter::LOCATION_LEGACY) {
            $destPath = public_path() . PackshotWriter::LEGACY_PATHS[$type];
            if (file_exists($destPath . $legacyFilename)) {
                $writer->recordExistingLegacy($this->game, $type, $legacyFilename);
                return $legacyFilename;
            }
        }

        $tempPath = $this->downloadToTemp($imageUrl, $legacyFilename);

        return $writer->store($this->game, $type, $tempPath, $legacyFilename);
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

    /**
     * Fetch a remote image into storage/tmp and return its full path.
     *
     * Stops at the temp file deliberately - placing it is PackshotWriter's job. This used to
     * rename() straight into public/img, which hard-coded local disk as the only destination.
     *
     * @return string full path to the downloaded temp file
     */
    public function downloadToTemp($remoteUrl, $destFilename)
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

        } catch (\ErrorException $e) {
            $errorData = [
                'origRemoteUrl' => $origRemoteUrl,
                'remoteUrl' => $remoteUrl,
                'storagePath' => $storagePath,
                'destFilename' => $destFilename,
            ];
            throw new \Exception('Error saving file: '.$e->getMessage().'; Error data: '.var_export($errorData, true));
        }

        return $storagePath.$destFilename;
    }
}
