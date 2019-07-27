<?php

namespace App\Construction\DbEdit;

use App\DbEditGame;

class GameBuilder
{
    /**
     * @var DbEditGame
     */
    private $dbEditGame;

    public function __construct()
    {
        $this->reset();
    }

    public function reset(): void
    {
        $this->dbEditGame = new DbEditGame;
    }

    public function getDbEditGame(): DbEditGame
    {
        return $this->dbEditGame;
    }

    public function setDbEditGame(DbEditGame $dbEditGame): void
    {
        $this->dbEditGame = $dbEditGame;
    }

    public function setUserId($userId): GameBuilder
    {
        $this->dbEditGame->user_id = $userId;
        return $this;
    }

    public function setGameId($gameId): GameBuilder
    {
        $this->dbEditGame->game_id = $gameId;
        return $this;
    }

    public function setDataToUpdate($dataToUpdate): GameBuilder
    {
        $this->dbEditGame->data_to_update = $dataToUpdate;
        return $this;
    }

    public function setCurrentData($currentData): GameBuilder
    {
        $this->dbEditGame->current_data = $currentData;
        return $this;
    }

    public function setNewData($newData): GameBuilder
    {
        $this->dbEditGame->new_data = $newData;
        return $this;
    }

    public function setStatus($status): GameBuilder
    {
        $this->dbEditGame->status = $status;
        return $this;
    }

    public function setChangeHistoryId($changeHistoryId): GameBuilder
    {
        $this->dbEditGame->change_history_id = $changeHistoryId;
        return $this;
    }

    public function setPointTransactionId($pointTransactionId): GameBuilder
    {
        $this->dbEditGame->point_transaction_id = $pointTransactionId;
        return $this;
    }
}