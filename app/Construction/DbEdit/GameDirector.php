<?php

namespace App\Construction\DbEdit;

use App\DbEditGame;
use Illuminate\Http\Request;

class GameDirector
{
    /**
     * @var GameBuilder
     */
    private $builder;

    public function setBuilder(GameBuilder $builder): void
    {
        $this->builder = $builder;
    }

    public function buildNew($params): void
    {
        $this->builder->setStatus(DbEditGame::STATUS_PENDING);
        $this->buildDbEditGame('new', $params);
    }

    public function buildExisting(DbEditGame $dbEditGame, $params): void
    {
        $this->builder->setDbEditGame($dbEditGame);
        $this->buildDbEditGame('existing', $params);
    }

    private function buildDbEditGame($mode, $params): void
    {
        if (array_key_exists('user_id', $params)) {
            $this->builder->setUserId($params['user_id']);
        }
        if (array_key_exists('game_id', $params)) {
            $this->builder->setGameId($params['game_id']);
        }
        if (array_key_exists('data_to_update', $params)) {
            $this->builder->setDataToUpdate($params['data_to_update']);
        }
        if (array_key_exists('current_data', $params)) {
            $this->builder->setCurrentData($params['current_data']);
        }
        if (array_key_exists('new_data', $params)) {
            $this->builder->setNewData($params['new_data']);
        }
        if ($mode == 'existing') {
            if (array_key_exists('status', $params)) {
                $this->builder->setStatus($params['status']);
            }
            if (array_key_exists('change_history_id', $params)) {
                $this->builder->setChangeHistoryId($params['change_history_id']);
            }
            if (array_key_exists('point_transaction_id', $params)) {
                $this->builder->setPointTransactionId($params['point_transaction_id']);
            }
        }
    }
}