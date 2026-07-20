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
        // PackshotWriter persists the result according to the configured storage location.
        $serviceImages = new Images($this->game);
        $serviceImages->setDSParsedItem($this->dsItem);
        $serviceImages->downloadSquare();
        $serviceImages->downloadHeader();
    }
}