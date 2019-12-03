<?php

namespace Tests\Unit;

use App\GameChangeHistory;
use Tests\TestCase;

use App\Construction\GameChangeHistory\Builder;

class BuilderTest extends TestCase
{
    public function testSourceEshopEurope()
    {
        $builder = new Builder();

        $gameChangeHistory = $builder->setSourceEshopEurope()->getGameChangeHistory();
        $this->assertEquals(GameChangeHistory::SOURCE_ESHOP_EUROPE, $gameChangeHistory->source);
    }

    public function testSourceEshopUS()
    {
        $builder = new Builder();

        $gameChangeHistory = $builder->setSourceEshopUS()->getGameChangeHistory();
        $this->assertEquals(GameChangeHistory::SOURCE_ESHOP_US, $gameChangeHistory->source);
    }

    public function testSourceWikipedia()
    {
        $builder = new Builder();

        $gameChangeHistory = $builder->setSourceWikipedia()->getGameChangeHistory();
        $this->assertEquals(GameChangeHistory::SOURCE_WIKIPEDIA, $gameChangeHistory->source);
    }

    public function testSourceAdmin()
    {
        $builder = new Builder();

        $gameChangeHistory = $builder->setSourceAdmin()->getGameChangeHistory();
        $this->assertEquals(GameChangeHistory::SOURCE_ADMIN, $gameChangeHistory->source);
    }

    public function testSourceMember()
    {
        $builder = new Builder();

        $gameChangeHistory = $builder->setSourceMember()->getGameChangeHistory();
        $this->assertEquals(GameChangeHistory::SOURCE_MEMBER, $gameChangeHistory->source);
    }

    public function testChangeTypeInsert()
    {
        $builder = new Builder();

        $gameChangeHistory = $builder->setChangeTypeInsert()->getGameChangeHistory();
        $this->assertEquals(GameChangeHistory::CHANGE_TYPE_INSERT, $gameChangeHistory->change_type);
    }

    public function testChangeTypeUpdate()
    {
        $builder = new Builder();

        $gameChangeHistory = $builder->setChangeTypeUpdate()->getGameChangeHistory();
        $this->assertEquals(GameChangeHistory::CHANGE_TYPE_UPDATE, $gameChangeHistory->change_type);
    }

    public function testChangeTypeDelete()
    {
        $builder = new Builder();

        $gameChangeHistory = $builder->setChangeTypeDelete()->getGameChangeHistory();
        $this->assertEquals(GameChangeHistory::CHANGE_TYPE_DELETE, $gameChangeHistory->change_type);
    }

    public function testTableNameCustomGames()
    {
        $tableName = 'games';

        $builder = new Builder();

        $gameChangeHistory = $builder->setTableName($tableName)->getGameChangeHistory();
        $this->assertEquals($tableName, $gameChangeHistory->affected_table_name);
    }

    public function testTableNameHelperGames()
    {
        $tableName = 'games';

        $builder = new Builder();

        $gameChangeHistory = $builder->setTableNameGames()->getGameChangeHistory();
        $this->assertEquals($tableName, $gameChangeHistory->affected_table_name);
    }

    public function testUserId()
    {
        $userId = 10;

        $builder = new Builder();

        $gameChangeHistory = $builder->setUserId($userId)->getGameChangeHistory();
        $this->assertEquals($userId, $gameChangeHistory->user_id);
    }

    public function testConvertArrayToJson()
    {
        $gameArray = ['title' => 'Mario', 'link_title' => 'mario-mario-mario'];
        $gameJson = json_encode($gameArray);

        $builder = new Builder();

        $convertedJson = $builder->convertArrayToJson($gameArray);
        $this->assertEquals($gameJson, $convertedJson);
    }

    public function testSetDataOld()
    {
        $builder = new Builder();

        $data = ['title' => 'Mario', 'link_title' => 'mario-mario-mario'];
        $dataJson = json_encode($data);

        $builder->setDataOld($data);
        $this->assertEquals($dataJson, $builder->getGameChangeHistory()->data_old);
    }

    public function testSetDataNew()
    {
        $builder = new Builder();

        $data = ['title' => 'Mario', 'link_title' => 'mario-mario-mario'];
        $dataJson = json_encode($data);

        $builder->setDataNew($data);
        $this->assertEquals($dataJson, $builder->getGameChangeHistory()->data_new);
    }

    public function testSetDataChanged()
    {
        $builder = new Builder();

        $data = ['title' => 'Mario', 'link_title' => 'mario-mario-mario'];
        $dataJson = json_encode($data);

        $builder->setDataChanged($data);
        $this->assertEquals($dataJson, $builder->getGameChangeHistory()->data_changed);
    }

    public function testGetArrayDifferencesFieldChanged()
    {
        $gameOrigArray = ['title' => 'Super Mario Odyssey'];
        $gameNewArray = ['title' => 'Super Luigi Odyssey'];
        $gameChangesArray = ['title' => 'Super Luigi Odyssey'];

        $builder = new Builder();

        $this->assertEquals($gameChangesArray, $builder->getArrayDifferences($gameOrigArray, $gameNewArray));

    }

    public function testGetArrayDifferencesFieldAdded()
    {
        $gameOrigArray = ['title' => 'Super Mario Odyssey'];
        $gameNewArray = ['title' => 'Super Mario Odyssey', 'link_title' => 'super-mario-odyssey'];
        $gameChangesArray = ['link_title' => 'super-mario-odyssey'];

        $builder = new Builder();

        $this->assertEquals($gameChangesArray, $builder->getArrayDifferences($gameOrigArray, $gameNewArray));

    }

    public function testGetArrayDifferencesFieldRemoved()
    {
        $gameOrigArray = ['title' => 'Super Mario Odyssey', 'link_title' => 'super-mario-odyssey'];
        $gameNewArray = ['title' => 'Super Mario Odyssey'];
        $gameChangesArray = []; //['link_title' => 'super-mario-odyssey'];

        $builder = new Builder();

        $this->assertEquals($gameChangesArray, $builder->getArrayDifferences($gameOrigArray, $gameNewArray));

    }

    public function testGetArrayDifferencesMultiple()
    {
        $gameOrigArray = ['title' => 'Super Mario Odyssey', 'link_title' => 'super-mario-odyssey', 'price_eshop' => '49.99', 'publisher' => 'Nintendo'];
        $gameNewArray = ['title' => 'Super Mario Odyssey', 'link_title' => 'super-mario-odyssey', 'price_eshop' => '39.99', 'publisher' => 'Nintendo EPD', 'players' => '1'];
        $gameChangesArray = ['price_eshop' => '39.99', 'publisher' => 'Nintendo EPD', 'players' => '1'];

        $builder = new Builder();

        $this->assertEquals($gameChangesArray, $builder->getArrayDifferences($gameOrigArray, $gameNewArray));

    }
}
