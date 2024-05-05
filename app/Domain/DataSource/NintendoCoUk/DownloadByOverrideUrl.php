<?php

namespace App\Domain\DataSource\NintendoCoUk;

use App\Services\DataSources\NintendoCoUk\Images;

class DownloadByOverrideUrl
{
    private $game;

    public function __construct($game)
    {
        $this->game = $game;
    }

    public function download($squareUrl, $headerUrl)
    {
        $serviceImages = new Images($this->game);
        if ($squareUrl) {
            $imageSquare = $serviceImages->downloadRemoteSquare($squareUrl, $this->game->id);
        } else {
            $imageSquare = null;
        }
        if ($headerUrl) {
            $imageHeader = $serviceImages->downloadRemoteHeader($headerUrl, $this->game->id);
        } else {
            $imageHeader = null;
        }
        if ($imageSquare) {
            $this->game->image_square = $imageSquare;
        }
        if ($imageHeader) {
            $this->game->image_header = $imageHeader;
        }
        $this->game->save();
    }
}