<?php

namespace App\Factories;

use App\Construction\User\PointTransactionBuilder;
use App\Construction\User\PointTransactionDirector;

class UserPointTransactionDirectorFactory
{
    public static function createNew($params)
    {
        $director = new PointTransactionDirector();
        $builder = new PointTransactionBuilder();
        $director->setBuilder($builder);
        $director->buildPointTransaction($params);
        $userPointTransaction = $builder->getUserPointTransaction();
        $userPointTransaction->save();
        return $userPointTransaction;
    }

    public static function buildParams($userId, $actionTypeId, $actionGameId = null, $pointsCredit = null, $pointsDebit = null)
    {
        $params = [];
        $params['user_id'] = $userId;
        $params['action_type_id'] = $actionTypeId;
        if ($actionGameId) {
            $params['action_game_id'] = $actionGameId;
        }
        if ($pointsCredit) {
            $params['points_credit'] = $pointsCredit;
        }
        if ($pointsDebit) {
            $params['points_debit'] = $pointsDebit;
        }
        return $params;
    }
}