<?php


namespace App\Domain\Game;

use App\Game;

class Repository
{
    /**
     * @param $title
     * @param null $excludeGameId
     * @return bool
     */
    public function titleExists($title, $excludeGameId = null): bool
    {
        $game = Game::where('title', $title);
        if ($excludeGameId) {
            $game = $game->where('id', '<>', $excludeGameId);
        }
        $game = $game->first();
        return $game != null;
    }

    /**
     * @param $title
     * @return Game
     */
    public function getByTitle($title): Game
    {
        return $game = Game::where('title', $title)->first();
    }
}