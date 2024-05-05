<?php

namespace App\Domain\DataSource\NintendoCoUk;

use App\Services\DataSources\NintendoCoUk\Images;

class DownloadByDataSource
{
    private $game;
    private $dsItem;

    public function __construct($game, $dsItem)
    {
        $this->game = $game;
        $this->dsItem = $dsItem;
    }

    public function download()
    {
        $serviceImages = new Images($this->game);
        $serviceImages->setDSParsedItem($this->dsItem);
        $serviceImages->downloadSquare();
        $serviceImages->downloadHeader();
        if ($serviceImages->squareDownloaded()) {
            $this->game->image_square = $serviceImages->getSquareFilename();
        }
        if ($serviceImages->headerDownloaded()) {
            $this->game->image_header = $serviceImages->getHeaderFilename();
        }
        $this->game->save();
    }
}