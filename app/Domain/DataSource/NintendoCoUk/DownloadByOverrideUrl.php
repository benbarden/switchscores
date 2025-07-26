<?php

namespace App\Domain\DataSource\NintendoCoUk;

use App\Services\DataSources\NintendoCoUk\Images;
use App\Domain\Game\Repository as GameRepository;

class DownloadByOverrideUrl
{
    private $game;

    public function __construct(
        $game,
        private GameRepository $repoGame
    )
    {
        $this->game = $game;
    }

    public function download($squareUrl, $headerUrl)
    {
        $gameId = $this->game->id;

        $serviceImages = new Images($this->game);
        if ($squareUrl) {
            $imageSquare = $serviceImages->downloadRemoteSquare($squareUrl, $gameId);
        } else {
            $imageSquare = null;
        }
        if ($headerUrl) {
            $imageHeader = $serviceImages->downloadRemoteHeader($headerUrl, $gameId);
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

        // Clear cache
        $this->repoGame->clearCacheCoreData($gameId);
    }
}