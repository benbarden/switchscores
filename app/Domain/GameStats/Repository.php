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

    /**
     * @return integer
     */
    public function totalToBeReleased()
    {
        $games = Game::where('eu_is_released', 0)
            ->whereRaw('DATE(games.eu_release_date) <= CURDATE()')
            ->count();

        return $games;
    }
}