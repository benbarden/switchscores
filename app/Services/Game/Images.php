<?php


namespace App\Services\Game;

use App\Game;


class Images
{
    const PATH_IMAGE_SQUARE = '/img/ps-square/';
    const PATH_IMAGE_HEADER = '/img/ps-header/';

    /**
     * @var Game
     */
    private $game;

    public function __construct(Game $game)
    {
        $this->game = $game;
    }

    public function deleteSquare()
    {
        $filePath = public_path().self::PATH_IMAGE_SQUARE;
        $fileName = $this->game->image_square;
        $fileToDelete = $filePath.$fileName;
        if (file_exists($fileToDelete)) {
            unlink($fileToDelete);
        }
    }

    public function deleteHeader()
    {
        $filePath = public_path().self::PATH_IMAGE_HEADER;
        $fileName = $this->game->image_header;
        $fileToDelete = $filePath.$fileName;
        if (file_exists($fileToDelete)) {
            unlink($fileToDelete);
        }
    }
}