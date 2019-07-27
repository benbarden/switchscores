<?php

namespace App\Construction\User;

use App\UserPointTransaction;

class PointTransactionBuilder
{
    /**
     * @var UserPointTransaction
     */
    private $userPointTransaction;

    public function __construct()
    {
        $this->reset();
    }

    public function reset(): void
    {
        $this->userPointTransaction = new UserPointTransaction;
    }

    public function getUserPointTransaction(): UserPointTransaction
    {
        return $this->userPointTransaction;
    }

    public function setUserPointTransaction(UserPointTransaction $userPointTransaction): void
    {
        $this->userPointTransaction = $userPointTransaction;
    }

    public function setUserId($userId): PointTransactionBuilder
    {
        $this->userPointTransaction->user_id = $userId;
        return $this;
    }

    public function setActionTypeId($actionTypeId): PointTransactionBuilder
    {
        $this->userPointTransaction->action_type_id = $actionTypeId;
        return $this;
    }

    public function setActionGameId($gameId): PointTransactionBuilder
    {
        $this->userPointTransaction->action_game_id = $gameId;
        return $this;
    }

    public function setPointsCredit($pointsCredit): PointTransactionBuilder
    {
        $this->userPointTransaction->points_credit = $pointsCredit;
        return $this;
    }

    public function setPointsDebit($pointsDebit): PointTransactionBuilder
    {
        $this->userPointTransaction->points_debit = $pointsDebit;
        return $this;
    }
}