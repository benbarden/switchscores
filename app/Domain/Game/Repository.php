<?php


namespace App\Domain\Game;

use App\Game;

class Repository
{
    public function find($id)
    {
        return Game::find($id);
    }

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
        return Game::where('title', $title)->first();
    }
}