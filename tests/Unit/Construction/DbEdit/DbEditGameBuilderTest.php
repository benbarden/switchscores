<?php

namespace Tests\Unit;

use Tests\TestCase;

use App\Construction\DbEdit\GameBuilder;
use App\DbEditGame;

class DbEditGameBuilderTest extends TestCase
{
    public function testUserId()
    {
        $userId = 60060;

        $builder = new GameBuilder();
        $dbEditGame = $builder->setUserId($userId)->getDbEditGame();
        $this->assertEquals($userId, $dbEditGame->user_id);
    }

    public function testGameId()
    {
        $gameId = 945;

        $builder = new GameBuilder();
        $builder->setGameId($gameId);

        $dbEditGame = $builder->getDbEditGame();
        $this->assertEquals($gameId, $dbEditGame->game_id);
    }

    public function testDataToUpdate()
    {
        $userId = 60060;
        $gameId = 945;
        $dataToUpdate = DbEditGame::DATA_PRIMARY_TYPE;

        $builder = new GameBuilder();
        $builder->setDataToUpdate($dataToUpdate)->setUserId($userId)->setGameId($gameId);

        $dbEditGame = $builder->getDbEditGame();
        $this->assertEquals($userId, $dbEditGame->user_id);
        $this->assertEquals($gameId, $dbEditGame->game_id);
        $this->assertEquals($dataToUpdate, $dbEditGame->data_to_update);
    }

    public function testCurrentDataNull()
    {
        $userId = 60060;
        $gameId = 945;
        $dataToUpdate = DbEditGame::DATA_PRIMARY_TYPE;
        $currentData = null;

        $builder = new GameBuilder();
        $builder->setDataToUpdate($dataToUpdate)
                ->setUserId($userId)
                ->setGameId($gameId)
                ->setCurrentData($currentData);

        $dbEditGame = $builder->getDbEditGame();
        $this->assertEquals($userId, $dbEditGame->user_id);
        $this->assertEquals($gameId, $dbEditGame->game_id);
        $this->assertEquals($dataToUpdate, $dbEditGame->data_to_update);
        $this->assertEquals($currentData, $dbEditGame->current_data);
    }

    public function testCurrentDataValue()
    {
        $userId = 60060;
        $gameId = 945;
        $dataToUpdate = DbEditGame::DATA_PRIMARY_TYPE;
        $currentData = 5;

        $builder = new GameBuilder();
        $builder->setDataToUpdate($dataToUpdate)
            ->setUserId($userId)
            ->setGameId($gameId)
            ->setCurrentData($currentData);

        $dbEditGame = $builder->getDbEditGame();
        $this->assertEquals($userId, $dbEditGame->user_id);
        $this->assertEquals($gameId, $dbEditGame->game_id);
        $this->assertEquals($dataToUpdate, $dbEditGame->data_to_update);
        $this->assertEquals($currentData, $dbEditGame->current_data);
    }

    public function testNewData()
    {
        $userId = 60060;
        $gameId = 945;
        $dataToUpdate = DbEditGame::DATA_PRIMARY_TYPE;
        $currentData = 5;
        $newData = 10;

        $builder = new GameBuilder();
        $builder->setDataToUpdate($dataToUpdate)
            ->setUserId($userId)
            ->setGameId($gameId)
            ->setCurrentData($currentData)
            ->setNewData($newData);

        $dbEditGame = $builder->getDbEditGame();
        $this->assertEquals($userId, $dbEditGame->user_id);
        $this->assertEquals($gameId, $dbEditGame->game_id);
        $this->assertEquals($dataToUpdate, $dbEditGame->data_to_update);
        $this->assertEquals($currentData, $dbEditGame->current_data);
        $this->assertEquals($newData, $dbEditGame->new_data);
    }

    public function testStatus()
    {
        $userId = 60060;
        $gameId = 945;
        $dataToUpdate = DbEditGame::DATA_PRIMARY_TYPE;
        $currentData = 5;
        $newData = 10;
        $status = DbEditGame::STATUS_PENDING;

        $builder = new GameBuilder();
        $builder->setDataToUpdate($dataToUpdate)
            ->setUserId($userId)
            ->setGameId($gameId)
            ->setCurrentData($currentData)
            ->setNewData($newData)
            ->setStatus($status);

        $dbEditGame = $builder->getDbEditGame();
        $this->assertEquals($userId, $dbEditGame->user_id);
        $this->assertEquals($gameId, $dbEditGame->game_id);
        $this->assertEquals($dataToUpdate, $dbEditGame->data_to_update);
        $this->assertEquals($currentData, $dbEditGame->current_data);
        $this->assertEquals($newData, $dbEditGame->new_data);
        $this->assertEquals($status, $dbEditGame->status);
    }

    public function testChangeHistoryId()
    {
        $userId = 60060;
        $gameId = 945;
        $dataToUpdate = DbEditGame::DATA_PRIMARY_TYPE;
        $currentData = 5;
        $newData = 10;
        $status = DbEditGame::STATUS_PENDING;
        $changeHistoryId = 55;

        $builder = new GameBuilder();
        $builder->setDataToUpdate($dataToUpdate)
            ->setUserId($userId)
            ->setGameId($gameId)
            ->setCurrentData($currentData)
            ->setNewData($newData)
            ->setStatus($status)
            ->setChangeHistoryId($changeHistoryId);

        $dbEditGame = $builder->getDbEditGame();
        $this->assertEquals($userId, $dbEditGame->user_id);
        $this->assertEquals($gameId, $dbEditGame->game_id);
        $this->assertEquals($dataToUpdate, $dbEditGame->data_to_update);
        $this->assertEquals($currentData, $dbEditGame->current_data);
        $this->assertEquals($newData, $dbEditGame->new_data);
        $this->assertEquals($status, $dbEditGame->status);
        $this->assertEquals($changeHistoryId, $dbEditGame->change_history_id);
    }

    public function testPointTransactionId()
    {
        $userId = 60060;
        $gameId = 945;
        $dataToUpdate = DbEditGame::DATA_PRIMARY_TYPE;
        $currentData = 5;
        $newData = 10;
        $status = DbEditGame::STATUS_PENDING;
        $changeHistoryId = 55;
        $pointTransactionId = 75;

        $builder = new GameBuilder();
        $builder->setDataToUpdate($dataToUpdate)
            ->setUserId($userId)
            ->setGameId($gameId)
            ->setCurrentData($currentData)
            ->setNewData($newData)
            ->setStatus($status)
            ->setChangeHistoryId($changeHistoryId)
            ->setPointTransactionId($pointTransactionId);

        $dbEditGame = $builder->getDbEditGame();
        $this->assertEquals($userId, $dbEditGame->user_id);
        $this->assertEquals($gameId, $dbEditGame->game_id);
        $this->assertEquals($dataToUpdate, $dbEditGame->data_to_update);
        $this->assertEquals($currentData, $dbEditGame->current_data);
        $this->assertEquals($newData, $dbEditGame->new_data);
        $this->assertEquals($status, $dbEditGame->status);
        $this->assertEquals($changeHistoryId, $dbEditGame->change_history_id);
        $this->assertEquals($pointTransactionId, $dbEditGame->point_transaction_id);
    }
}
