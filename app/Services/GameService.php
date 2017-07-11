<?php


namespace App\Services;

use App\Game;


class GameService
{
    public function getAll()
    {
        $gamesList = Game::orderBy('title', 'asc')->get();
        return $gamesList;
    }

    public function getAllReleased()
    {
        $gamesList = Game::where('upcoming', 0)->orderBy('title', 'asc')->get();
        return $gamesList;
    }

    public function getAllUpcoming()
    {
        $gamesList = Game::where('upcoming', 1)->orderBy('title', 'asc')->get();
        return $gamesList;
    }
}