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
        if ($gameOrig) {
            $gameChangeHistoryBuilder->setGameOriginal($gameOrig);
        }
        $gameChangeHistoryDirector->setBuilder($gameChangeHistoryBuilder);
        if ($table == 'games') {
            $gameChangeHistoryDirector->setTableNameGames();
        }
        if ($gameOrig) {
            $gameChangeHistoryDirector->buildAdminUpdate();
        } else {
            $gameChangeHistoryDirector->buildAdminInsert();
        }
        $gameChangeHistoryDirector->setUserId($userId);
        $gameChangeHistory = $gameChangeHistoryBuilder->getGameChangeHistory();

        // Only save if there were changes made
        $dataChanged = $gameChangeHistory->data_changed;
        if ($dataChanged) {
            $dataChangedArray = json_decode($dataChanged);
            if ($dataChangedArray) {
                $gameChangeHistory->save();
            }
        }
    }
}