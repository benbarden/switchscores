<?php

namespace App\Domain\DataSource\NintendoCoUk;

use App\Services\DataSources\NintendoCoUk\Images;
use App\Domain\Game\Repository as GameRepository;
use App\Models\Game;

class DownloadByOverrideUrl
{
    private $game;

    public function __construct(
        private GameRepository $repoGame
    )
    {
    }

    public function setGame(Game $game)
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