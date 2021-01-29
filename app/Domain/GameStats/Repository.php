<?php


namespace App\Domain\GameStats;


use App\Game;

class Repository
{
    /**
     * @return integer
     */
    public function totalReleased()
    {
        return Game::where('eu_is_released', 1)->count();
    }

    /**
     * @return integer
     */
    public function totalRanked()
    {
        return Game::whereNotNull('game_rank')->count();
    }
}