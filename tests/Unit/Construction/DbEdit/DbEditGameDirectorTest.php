<?php

namespace Tests\Unit\Construction\DbEdit;

use Tests\TestCase;

use App\DbEditGame;

use App\Construction\DbEdit\GameBuilder;
use App\Construction\DbEdit\GameDirector;

class DbEditGameDirectorTest extends TestCase
{
    public function testSetUserId()
    {
        $userId = 55010;

        $director = new GameDirector();
        $builder = new GameBuilder();
        $director->setBuilder($builder);

        $builder->setUserId($userId);

        $this->assertEquals($userId, $builder->getDbEditGame()->user_id);
    }

    public function testBuildNewCategory()
    {
        $statusPending = DbEditGame::STATUS_PENDING;
        $statusApproved = DbEditGame::STATUS_APPROVED;
        $statusDenied = DbEditGame::STATUS_DENIED;

        $userId = 60060;
        $gameId = 945;
        $dataToUpdate = DbEditGame::DATA_CATEGORY;
        $currentData = 5;
        $newData = 10;
        $pointTransactionId = null;

        $params = [
            'user_id' => $userId,
            'game_id' => $gameId,
            'data_to_update' => $dataToUpdate,
            'current_data' => $currentData,
            'new_data' => $newData,
            //'point_transaction_id' => $pointTransactionId,
        ];

        $director = new GameDirector();
        $builder = new GameBuilder();
        $director->setBuilder($builder);

        $director->buildNew($params);

        $dbEditGame = $builder->getDbEditGame();
        $this->assertEquals($userId, $dbEditGame->user_id);
        $this->assertEquals($gameId, $dbEditGame->game_id);
        $this->assertEquals($dataToUpdate, $dbEditGame->data_to_update);
        $this->assertEquals($currentData, $dbEditGame->current_data);
        $this->assertEquals($newData, $dbEditGame->new_data);
        $this->assertEquals($statusPending, $dbEditGame->status);
        $this->assertEquals($pointTransactionId, $dbEditGame->point_transaction_id);
    }

    public function testBuildCategoryApproved()
    {
        $statusPending = DbEditGame::STATUS_PENDING;
        $statusApproved = DbEditGame::STATUS_APPROVED;
        $statusDenied = DbEditGame::STATUS_DENIED;

        $userId = 60060;
        $gameId = 945;
        $dataToUpdate = DbEditGame::DATA_CATEGORY;
        $currentData = 5;
        $newData = 10;
        // NOTE: these can't be set at creation, but we'll use them later
        // Best to test them like this to make sure they aren't set when they shouldn't be
        $pointTransactionId = 75;

        $params = [
            'user_id' => $userId,
            'game_id' => $gameId,
            'data_to_update' => $dataToUpdate,
            'current_data' => $currentData,
            'new_data' => $newData,
            'point_transaction_id' => $pointTransactionId,
        ];


        $director = new GameDirector();
        $builder = new GameBuilder();
        $director->setBuilder($builder);

        $director->buildNew($params);

        $dbEditGame = $builder->getDbEditGame();
        $this->assertEquals($userId, $dbEditGame->user_id);
        $this->assertEquals($gameId, $dbEditGame->game_id);
        $this->assertEquals($dataToUpdate, $dbEditGame->data_to_update);
        $this->assertEquals($currentData, $dbEditGame->current_data);
        $this->assertEquals($newData, $dbEditGame->new_data);
        $this->assertEquals($statusPending, $dbEditGame->status);
        $this->assertEquals(null, $dbEditGame->point_transaction_id);

        // Update status to Approved
        $params = [
            'status' => $statusApproved,
            'point_transaction_id' => $pointTransactionId,
        ];
        $director->buildExisting($dbEditGame, $params);

        $dbEditGame = $builder->getDbEditGame();
        $this->assertEquals($userId, $dbEditGame->user_id);
        $this->assertEquals($gameId, $dbEditGame->game_id);
        $this->assertEquals($dataToUpdate, $dbEditGame->data_to_update);
        $this->assertEquals($currentData, $dbEditGame->current_data);
        $this->assertEquals($newData, $dbEditGame->new_data);
        $this->assertEquals($statusApproved, $dbEditGame->status);
        $this->assertEquals($pointTransactionId, $dbEditGame->point_transaction_id);
    }

    public function testBuildCategoryDenied()
    {
        $statusPending = DbEditGame::STATUS_PENDING;
        $statusApproved = DbEditGame::STATUS_APPROVED;
        $statusDenied = DbEditGame::STATUS_DENIED;

        $userId = 60060;
        $gameId = 945;
        $dataToUpdate = DbEditGame::DATA_CATEGORY;
        $currentData = 5;
        $newData = 10;
        $pointTransactionId = null;

        $params = [
            'user_id' => $userId,
            'game_id' => $gameId,
            'data_to_update' => $dataToUpdate,
            'current_data' => $currentData,
            'new_data' => $newData,
            //'point_transaction_id' => $pointTransactionId,
        ];

        $director = new GameDirector();
        $builder = new GameBuilder();
        $director->setBuilder($builder);

        $director->buildNew($params);

        $dbEditGame = $builder->getDbEditGame();
        $this->assertEquals($userId, $dbEditGame->user_id);
        $this->assertEquals($gameId, $dbEditGame->game_id);
        $this->assertEquals($dataToUpdate, $dbEditGame->data_to_update);
        $this->assertEquals($currentData, $dbEditGame->current_data);
        $this->assertEquals($newData, $dbEditGame->new_data);
        $this->assertEquals($statusPending, $dbEditGame->status);
        $this->assertEquals(null, $dbEditGame->point_transaction_id);

        // Update status to Denied
        $params = [
            'status' => $statusDenied,
            // These fields aren't needed but we'll test them anyway
            'point_transaction_id' => $pointTransactionId,
        ];
        $director->buildExisting($dbEditGame, $params);

        $dbEditGame = $builder->getDbEditGame();
        $this->assertEquals($userId, $dbEditGame->user_id);
        $this->assertEquals($gameId, $dbEditGame->game_id);
        $this->assertEquals($dataToUpdate, $dbEditGame->data_to_update);
        $this->assertEquals($currentData, $dbEditGame->current_data);
        $this->assertEquals($newData, $dbEditGame->new_data);
        $this->assertEquals($statusDenied, $dbEditGame->status);
        $this->assertEquals($pointTransactionId, $dbEditGame->point_transaction_id);
    }
}
