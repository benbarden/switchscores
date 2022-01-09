<?php

namespace App\Factories\DataSource\NintendoCoUk;

use App\Models\DataSourceParsed;
use App\Models\Game;
use App\Services\DataSources\NintendoCoUk\Images;

class DownloadImageFactory
{
    public static function downloadImages(Game $game, DataSourceParsed $dsItem)
    {
        $serviceImages = new Images($game, $dsItem);
        $serviceImages->downloadSquare();
        $serviceImages->downloadHeader();
        if ($serviceImages->squareDownloaded()) {
            $game->image_square = $serviceImages->getSquareFilename();
        }
        if ($serviceImages->headerDownloaded()) {
            $game->image_header = $serviceImages->getHeaderFilename();
        }
        $game->save();
    }
}