<?php

namespace App\Factories\DataSource\NintendoCoUk;

use App\Models\DataSourceParsed;
use App\Models\Game;
use App\Services\DataSources\NintendoCoUk\Images;

class DownloadImageFactory
{
    public static function downloadImages(Game $game, DataSourceParsed $dsItem)
    {
        $serviceImages = new Images($game);
        $serviceImages->setDSParsedItem($dsItem);
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

    public static function downloadFromStoreUrl(Game $game, $squareUrl = null, $headerUrl = null)
    {
        $serviceImages = new Images($game);
        if ($squareUrl) {
            $imageSquare = $serviceImages->downloadRemoteSquare($squareUrl, $game->id);
        } else {
            $imageSquare = null;
        }
        if ($headerUrl) {
            $imageHeader = $serviceImages->downloadRemoteHeader($headerUrl, $game->id);
        } else {
            $imageHeader = null;
        }
        if ($imageSquare) {
            $game->image_square = $imageSquare;
        }
        if ($imageHeader) {
            $game->image_header = $imageHeader;
        }
        $game->save();
    }
}