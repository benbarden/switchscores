<?php

namespace App\Factories;

use App\Construction\Game\GameBuilder;
use App\Construction\Game\GameDirector;

class GameDirectorFactory
{
    public static function createNew($params)
    {
        $gameDirector = new GameDirector();
        $gameBuilder = new GameBuilder();
        $gameDirector->setBuilder($gameBuilder);
        $gameDirector->buildNewGame($params);
        $game = $gameBuilder->getGame();
        $game->save();
        return $game;
    }

    public static function updateExisting($game, $params)
    {
        $gameDirector = new GameDirector();
        $gameBuilder = new GameBuilder();
        $gameDirector->setBuilder($gameBuilder);
        $gameDirector->buildExistingGame($game, $params);
        $game = $gameBuilder->getGame();
        $game->save();
    }
}