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

        // Remove leading/trailing spaces, CRLF etc
        $squareUrl = trim($squareUrl);
        $headerUrl = trim($headerUrl);

        // PackshotWriter persists the result (legacy column or game_images row) according to
        // the configured location, so nothing is assigned to games.image_* here. Setting those
        // columns under `spaces` would point the resolver's legacy fallback at a file that was
        // never written locally.
        $serviceImages = new Images($this->game);
        if ($squareUrl) {
            $serviceImages->downloadRemoteSquare($squareUrl, $gameId);
        }
        if ($headerUrl) {
            $serviceImages->downloadRemoteHeader($headerUrl, $gameId);
        }

        // Clear cache
        $this->repoGame->clearCacheCoreData($gameId);
    }
}