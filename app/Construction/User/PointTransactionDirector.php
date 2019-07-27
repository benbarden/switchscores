<?php

namespace App\Construction\User;

use App\Construction\User\PointTransactionBuilder;

class PointTransactionDirector
{
    /**
     * @var PointTransactionBuilder
     */
    private $builder;

    public function setBuilder(PointTransactionBuilder $builder): void
    {
        $this->builder = $builder;
    }

    public function buildPointTransaction($params): void
    {
        if (array_key_exists('user_id', $params)) {
            $this->builder->setUserId($params['user_id']);
        }
        if (array_key_exists('action_type_id', $params)) {
            $this->builder->setActionTypeId($params['action_type_id']);
        }
        if (array_key_exists('action_game_id', $params)) {
            $this->builder->setActionGameId($params['action_game_id']);
        }
        if (array_key_exists('points_credit', $params)) {
            $this->builder->setPointsCredit($params['points_credit']);
        }
        if (array_key_exists('points_debit', $params)) {
            $this->builder->setPointsDebit($params['points_debit']);
        }
    }
}