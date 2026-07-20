<?php

namespace App\Factories\DataSource\NintendoCoUk;

use App\Models\DataSourceParsed;
use App\Models\Game;
use App\Services\DataSources\NintendoCoUk\Images;

/**
 * @deprecated
 */
class DownloadImageFactory
{
    /**
     * @deprecated
     * @param Game $game
     * @param DataSourceParsed $dsItem
     * @return void
     */
    public static function downloadImages(Game $game, DataSourceParsed $dsItem)
    {
        // PackshotWriter persists the result according to the configured storage location.
        $serviceImages = new Images($game);
        $serviceImages->setDSParsedItem($dsItem);
        $serviceImages->downloadSquare();
        $serviceImages->downloadHeader();
    }

    /**
     * @deprecated
     * @param Game $game
     * @param $squareUrl
     * @param $headerUrl
     * @return void
     */
    public static function downloadFromStoreUrl(Game $game, $squareUrl = null, $headerUrl = null)
    {
        // PackshotWriter persists the result according to the configured storage location.
        $serviceImages = new Images($game);
        if ($squareUrl) {
            $serviceImages->downloadRemoteSquare($squareUrl, $game->id);
        }
        if ($headerUrl) {
            $serviceImages->downloadRemoteHeader($headerUrl, $game->id);
        }
    }
}