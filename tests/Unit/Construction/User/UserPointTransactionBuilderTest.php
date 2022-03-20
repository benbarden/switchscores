<?php

namespace Tests\Unit\Construction\User;

use App\Construction\User\PointTransactionBuilder;
use App\Models\UserPointTransaction;
use Tests\TestCase;

class UserPointTransactionBuilderTest extends TestCase
{
    public function testUserId()
    {
        $userId = 60060;

        $builder = new PointTransactionBuilder();
        $userPointTransaction = $builder->setUserId($userId)->getUserPointTransaction();
        $this->assertEquals($userId, $userPointTransaction->user_id);
    }

    public function testActionTypeId()
    {
        $userId = 60060;
        $actionTypeId = UserPointTransaction::ACTION_TYPE_REGISTER;

        $builder = new PointTransactionBuilder();
        $builder->setUserId($userId)
                ->setActionTypeId($actionTypeId);

        $userPointTransaction = $builder->getUserPointTransaction();
        $this->assertEquals($userId, $userPointTransaction->user_id);
        $this->assertEquals($actionTypeId, $userPointTransaction->action_type_id);
    }

    public function testActionGameId()
    {
        $userId = 60060;
        $actionTypeId = UserPointTransaction::ACTION_TYPE_REGISTER;
        $actionGameId = 945;

        $builder = new PointTransactionBuilder();
        $builder->setUserId($userId)
            ->setActionTypeId($actionTypeId)
            ->setActionGameId($actionGameId);

        $userPointTransaction = $builder->getUserPointTransaction();
        $this->assertEquals($userId, $userPointTransaction->user_id);
        $this->assertEquals($actionTypeId, $userPointTransaction->action_type_id);
        $this->assertEquals($actionGameId, $userPointTransaction->action_game_id);
    }

    public function testPointsCredit()
    {
        $userId = 60060;
        $actionTypeId = UserPointTransaction::ACTION_TYPE_REGISTER;
        $actionGameId = 945;
        $pointsCredit = 100;

        $builder = new PointTransactionBuilder();
        $builder->setUserId($userId)
            ->setActionTypeId($actionTypeId)
            ->setActionGameId($actionGameId)
            ->setPointsCredit($pointsCredit);

        $userPointTransaction = $builder->setUserId($userId)->getUserPointTransaction();
        $this->assertEquals($userId, $userPointTransaction->user_id);
        $this->assertEquals($actionTypeId, $userPointTransaction->action_type_id);
        $this->assertEquals($actionGameId, $userPointTransaction->action_game_id);
        $this->assertEquals($pointsCredit, $userPointTransaction->points_credit);
    }

    public function testPointsDebit()
    {
        $userId = 60060;
        $actionTypeId = UserPointTransaction::ACTION_TYPE_REGISTER;
        $actionGameId = 945;
        $pointsDebit = 100;

        $builder = new PointTransactionBuilder();
        $builder->setUserId($userId)
            ->setActionTypeId($actionTypeId)
            ->setActionGameId($actionGameId)
            ->setPointsDebit($pointsDebit);

        $userPointTransaction = $builder->setUserId($userId)->getUserPointTransaction();
        $this->assertEquals($userId, $userPointTransaction->user_id);
        $this->assertEquals($actionTypeId, $userPointTransaction->action_type_id);
        $this->assertEquals($actionGameId, $userPointTransaction->action_game_id);
        $this->assertEquals($pointsDebit, $userPointTransaction->points_debit);
    }
}
