<?php

namespace Tests\Unit;

use App\Game;
use App\GameChangeHistory;
use Tests\TestCase;

use App\Construction\GameChangeHistory\Builder;
use App\Construction\GameChangeHistory\Director;

class DirectorTest extends TestCase
{
    public function testWikipediaInsert()
    {
        $director = new Director();
        $builder = new Builder();
        $director->setBuilder($builder);

        $director->buildWikipediaInsert();

        $gameChangeHistory = $builder->getGameChangeHistory();

        $this->assertEquals(GameChangeHistory::SOURCE_WIKIPEDIA, $gameChangeHistory->source);
        $this->assertEquals(GameChangeHistory::CHANGE_TYPE_INSERT, $gameChangeHistory->change_type);
    }

    public function testWikipediaUpdate()
    {
        $director = new Director();
        $builder = new Builder();
        $director->setBuilder($builder);

        $director->buildWikipediaUpdate();

        $gameChangeHistory = $builder->getGameChangeHistory();

        $this->assertEquals(GameChangeHistory::SOURCE_WIKIPEDIA, $gameChangeHistory->source);
        $this->assertEquals(GameChangeHistory::CHANGE_TYPE_UPDATE, $gameChangeHistory->change_type);
    }

    public function testEshopEuropeUpdate()
    {
        $director = new Director();
        $builder = new Builder();
        $director->setBuilder($builder);

        $director->buildEshopEuropeUpdate();

        $gameChangeHistory = $builder->getGameChangeHistory();

        $this->assertEquals(GameChangeHistory::SOURCE_ESHOP_EUROPE, $gameChangeHistory->source);
        $this->assertEquals(GameChangeHistory::CHANGE_TYPE_UPDATE, $gameChangeHistory->change_type);
    }

    public function testAdminInsert()
    {
        $director = new Director();
        $builder = new Builder();
        $director->setBuilder($builder);

        $director->buildAdminInsert();

        $gameChangeHistory = $builder->getGameChangeHistory();

        $this->assertEquals(GameChangeHistory::SOURCE_ADMIN, $gameChangeHistory->source);
        $this->assertEquals(GameChangeHistory::CHANGE_TYPE_INSERT, $gameChangeHistory->change_type);
    }

    public function testAdminUpdate()
    {
        $director = new Director();
        $builder = new Builder();
        $director->setBuilder($builder);

        $director->buildAdminUpdate();

        $gameChangeHistory = $builder->getGameChangeHistory();

        $this->assertEquals(GameChangeHistory::SOURCE_ADMIN, $gameChangeHistory->source);
        $this->assertEquals(GameChangeHistory::CHANGE_TYPE_UPDATE, $gameChangeHistory->change_type);
    }

    public function testAdminDelete()
    {
        $director = new Director();
        $builder = new Builder();
        $director->setBuilder($builder);

        $director->buildAdminDelete();

        $gameChangeHistory = $builder->getGameChangeHistory();

        $this->assertEquals(GameChangeHistory::SOURCE_ADMIN, $gameChangeHistory->source);
        $this->assertEquals(GameChangeHistory::CHANGE_TYPE_DELETE, $gameChangeHistory->change_type);
    }

    public function testBuildDataForInsert()
    {
        $gameArray = ['title' => 'Super Mario Odyssey', 'link_title' => 'super-mario-odyssey', 'price_eshop' => '49.99'];
        $gameJson = json_encode($gameArray);
        $game = new Game($gameArray);

        $director = new Director();
        $builder = new Builder();

        $builder->setGame($game);

        $director->setBuilder($builder);

        $director->buildDataForInsert();
        $gameChangeHistory = $builder->getGameChangeHistory();

        $this->assertEquals($gameArray, $game->toArray());

        $this->assertEquals(null, $gameChangeHistory->data_old);
        $this->assertEquals($gameJson, $gameChangeHistory->data_new);
        $this->assertEquals(null, $gameChangeHistory->data_changed);
    }

    public function testBuildDataForUpdate()
    {
        $gameOrigArray = ['title' => 'Super Mario Odyssey', 'link_title' => 'super-mario-odyssey', 'price_eshop' => '49.99', 'publisher' => 'Nintendo'];
        $gameOrigJson = json_encode($gameOrigArray);
        $gameOrig = new Game($gameOrigArray);

        $gameArray = ['title' => 'Super Mario Odyssey', 'link_title' => 'super-mario-odyssey', 'price_eshop' => '39.99', 'publisher' => 'Nintendo EPD', 'players' => '1'];
        $gameJson = json_encode($gameArray);
        $game = new Game($gameArray);

        $gameChangesArray = ['price_eshop' => '39.99', 'publisher' => 'Nintendo EPD', 'players' => '1'];
        $gameChangesJson = json_encode($gameChangesArray);

        $director = new Director();
        $builder = new Builder();

        $builder->setGame($game);
        $builder->setGameOriginal($gameOrig);

        $director->setBuilder($builder);

        $director->buildDataForUpdate();
        $gameChangeHistory = $builder->getGameChangeHistory();

        $this->assertEquals($gameArray, $game->toArray());

        $this->assertEquals($gameOrigJson, $gameChangeHistory->data_old);
        $this->assertEquals($gameJson, $gameChangeHistory->data_new);
        $this->assertEquals($gameChangesJson, $gameChangeHistory->data_changed);
    }

    public function testBuildDataForDelete()
    {
        $gameArray = ['title' => 'Super Mario Odyssey', 'link_title' => 'super-mario-odyssey', 'price_eshop' => '49.99'];
        $gameJson = json_encode($gameArray);
        $game = new Game($gameArray);

        $director = new Director();
        $builder = new Builder();

        $builder->setGame($game);

        $director->setBuilder($builder);

        $director->buildDataForDelete();
        $gameChangeHistory = $builder->getGameChangeHistory();

        $this->assertEquals($gameArray, $game->toArray());

        $this->assertEquals($gameJson, $gameChangeHistory->data_old);
        $this->assertEquals(null, $gameChangeHistory->data_new);
        $this->assertEquals(null, $gameChangeHistory->data_changed);
    }

    public function testSetTableNameGames()
    {
        $director = new Director();
        $builder = new Builder();

        $director->setBuilder($builder);

        $director->setTableNameGames();

        $this->assertEquals('games', $builder->getGameChangeHistory()->affected_table_name);
    }
}
