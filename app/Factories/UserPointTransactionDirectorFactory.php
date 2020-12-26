<?php

namespace App\Factories;

use App\Construction\User\PointTransactionBuilder;
use App\Construction\User\PointTransactionDirector;

use App\UserPointTransaction;

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

    public static function addForUserRegistration($userId)
    {
        $actionTypeId = UserPointTransaction::ACTION_TYPE_REGISTER;
        $pointsCredit = UserPointTransaction::POINTS_REGISTER;

        $params = self::buildParams($userId, $actionTypeId, null, $pointsCredit);
        $userPointTransaction = self::createNew($params);
        return $userPointTransaction;
    }

    public static function addForQuickReview($userId, $gameId)
    {
        $actionTypeId = UserPointTransaction::ACTION_QUICK_REVIEW_ADD;
        $pointsCredit = UserPointTransaction::POINTS_QUICK_REVIEW_ADD;

        $params = self::buildParams($userId, $actionTypeId, $gameId, $pointsCredit);
        $userPointTransaction = self::createNew($params);
        return $userPointTransaction;
    }

    public static function addForGameCategorySuggestion($userId, $gameId)
    {
        $actionTypeId = UserPointTransaction::ACTION_DB_CATEGORY;
        $pointsCredit = UserPointTransaction::POINTS_DB_EDIT;

        $params = self::buildParams($userId, $actionTypeId, $gameId, $pointsCredit);
        $userPointTransaction = self::createNew($params);
        return $userPointTransaction;
    }
}