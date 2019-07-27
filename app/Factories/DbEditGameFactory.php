<?php

namespace App\Factories;

use App\Construction\DbEdit\GameBuilder;
use App\Construction\DbEdit\GameDirector;

class DbEditGameFactory
{
    public static function createNew($params)
    {
        $director = new GameDirector();
        $builder = new GameBuilder();
        $director->setBuilder($builder);
        $director->buildNew($params);
        $dbEditGame = $builder->getDbEditGame();
        $dbEditGame->save();
        return $dbEditGame;
    }

    public static function buildParams($userId, $gameId, $dataToUpdate, $currentData, $newData)
    {
        $params = [];
        $params['user_id'] = $userId;
        $params['game_id'] = $gameId;
        $params['data_to_update'] = $dataToUpdate;
        if ($currentData) {
            $params['current_data'] = $currentData;
        }
        if ($newData) {
            $params['new_data'] = $newData;
        }
        return $params;
    }
}