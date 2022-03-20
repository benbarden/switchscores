<?php

namespace Tests\Unit\Construction\User;

use App\Construction\User\PointTransactionBuilder;
use App\Construction\User\PointTransactionDirector;
use App\Models\UserPointTransaction;
use Tests\TestCase;

class UserPointTransactionDirectorTest extends TestCase
{
    public function testSetUserId()
    {
        $userId = 55010;

        $director = new PointTransactionDirector();
        $builder = new PointTransactionBuilder();
        $director->setBuilder($builder);

        $builder->setUserId($userId);

        $this->assertEquals($userId, $builder->getUserPointTransaction()->user_id);
    }

    public function testBuildCreditTransaction()
    {
        $userId = 74747;
        $actionTypeId = UserPointTransaction::ACTION_TYPE_REGISTER;
        $actionGameId = 945;
        $pointsCredit = 100;
        $pointsDebit = 0;
        $params = [
            'user_id' => $userId,
            'action_type_id' => $actionTypeId,
            'action_game_id' => $actionGameId,
            'points_credit' => $pointsCredit,
            //'points_debit' => $pointsDebit,
        ];

        $director = new PointTransactionDirector();
        $builder = new PointTransactionBuilder();
        $director->setBuilder($builder);

        $director->buildPointTransaction($params);

        $builderPointTransaction = $builder->getUserPointTransaction();

        $this->assertEquals($userId, $builderPointTransaction->user_id);
        $this->assertEquals($actionTypeId, $builderPointTransaction->action_type_id);
        $this->assertEquals($actionGameId, $builderPointTransaction->action_game_id);
        $this->assertEquals($pointsCredit, $builderPointTransaction->points_credit);
        $this->assertEquals($pointsDebit, $builderPointTransaction->points_debit);
    }

    public function testBuildDebitTransaction()
    {
        $userId = 74747;
        $actionTypeId = UserPointTransaction::ACTION_TYPE_REGISTER;
        $actionGameId = 945;
        $pointsCredit = 0;
        $pointsDebit = 100;
        $params = [
            'user_id' => $userId,
            'action_type_id' => $actionTypeId,
            'action_game_id' => $actionGameId,
            //'points_credit' => $pointsCredit,
            'points_debit' => $pointsDebit,
        ];

        $director = new PointTransactionDirector();
        $builder = new PointTransactionBuilder();
        $director->setBuilder($builder);

        $director->buildPointTransaction($params);

        $builderPointTransaction = $builder->getUserPointTransaction();

        $this->assertEquals($userId, $builderPointTransaction->user_id);
        $this->assertEquals($actionTypeId, $builderPointTransaction->action_type_id);
        $this->assertEquals($actionGameId, $builderPointTransaction->action_game_id);
        $this->assertEquals($pointsCredit, $builderPointTransaction->points_credit);
        $this->assertEquals($pointsDebit, $builderPointTransaction->points_debit);
    }
}
