<?php

namespace App\Factories;

use App\Construction\GameChangeHistory\Director as GameChangeHistoryDirector;
use App\Construction\GameChangeHistory\Builder as GameChangeHistoryBuilder;

class GameChangeHistoryFactory
{
    public static function makeHistory($game, $gameOrig, $userId, $table)
    {
        $gameChangeHistoryDirector = new GameChangeHistoryDirector();
        $gameChangeHistoryBuilder = new GameChangeHistoryBuilder();

        $gameChangeHistoryBuilder->setGame($game);
        $gameChangeHistoryBuilder->setGameOriginal($gameOrig);
        $gameChangeHistoryDirector->setBuilder($gameChangeHistoryBuilder);
        if ($table == 'games') {
            $gameChangeHistoryDirector->setTableNameGames();
        }
        $gameChangeHistoryDirector->buildAdminUpdate();
        $gameChangeHistoryDirector->setUserId($userId);
        $gameChangeHistory = $gameChangeHistoryBuilder->getGameChangeHistory();
        $gameChangeHistory->save();
    }
}