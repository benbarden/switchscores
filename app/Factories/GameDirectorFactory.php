<?php

namespace App\Factories;

use App\Construction\Game\GameBuilder;
use App\Construction\Game\GameDirector;

class GameDirectorFactory
{
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