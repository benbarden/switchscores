<?php


namespace App\Factories\Tests;

use App\Game;

class GameFactory
{
    public static function makeSimpleGameForNoChange(Game $game)
    {
        $game->eu_release_date = '2017-03-03';
        $game->eu_is_released = '1';
        return $game;
    }

    public static function makeFullCollectionWithChanges(Game $game)
    {
        $game->eu_release_date = '2018-02-01';
        $game->eu_is_released = '1';
        $game->us_release_date = '2018-03-01';
        $game->jp_release_date = '2018-04-01';
        return $game;
    }
}