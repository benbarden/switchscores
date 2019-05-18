<?php

namespace Tests\Unit\Services\Eshop;

use App\EshopEuropeGame;
use App\Game;
use App\Services\Eshop\Europe\UpdateGameData;

use Illuminate\Support\Collection;
use Tests\TestCase;
#use Illuminate\Foundation\Testing\DatabaseMigrations;
#use Illuminate\Foundation\Testing\DatabaseTransactions;

class UpdateGameDataTest extends TestCase
{
    /**
     * @var UpdateGameData
     */
    private $serviceUpdateGameData;

    public function setUp()
    {
        $this->serviceUpdateGameData = new UpdateGameData();

        parent::setUp();
    }

    public function tearDown()
    {
        unset($this->serviceUpdateGameData);

        parent::tearDown();
    }

    public function testNewGameHasNotChanged()
    {
        $game = new Game;
        $this->serviceUpdateGameData->setGame($game);

        $this->assertFalse($this->serviceUpdateGameData->hasGameChanged());
    }

    public function testGameDataValues()
    {
        $gameTitle = 'Yoshi\'s Crafted World';
        $gameLinkTitle = 'yoshis-crafted-world';

        $game = new Game;
        $game->title = $gameTitle;
        $game->link_title = $gameLinkTitle;

        $this->serviceUpdateGameData->setGame($game);

        $serviceGameItem = $this->serviceUpdateGameData->getGame();

        $this->assertEquals($gameTitle, $serviceGameItem->title);
        $this->assertEquals($gameLinkTitle, $serviceGameItem->link_title);
    }

    public function testUpdateNoOfPlayersNoValuesSet()
    {
        $gameTitle = 'Mario Kart 8 Deluxe';
        $game = new Game;
        $game->title = $gameTitle;

        $eshopItem = new EshopEuropeGame;
        //$eshopItem->players_from

        $this->serviceUpdateGameData->setGame($game);
        $this->serviceUpdateGameData->setEshopItem($eshopItem);
        $this->serviceUpdateGameData->updateNoOfPlayers();

        $serviceGameItem = $this->serviceUpdateGameData->getGame();

        $this->assertFalse($this->serviceUpdateGameData->hasGameChanged());
        $this->assertEquals("", $serviceGameItem->players);
    }

    public function testUpdateNoOfPlayersNoValuesSetNoChange()
    {
        $gameTitle = 'Mario Kart 8 Deluxe';
        $game = new Game;
        $game->title = $gameTitle;
        $game->players = "1";

        $eshopItem = new EshopEuropeGame;
        //$eshopItem->players_from

        $this->serviceUpdateGameData->setGame($game);
        $this->serviceUpdateGameData->setEshopItem($eshopItem);
        $this->serviceUpdateGameData->updateNoOfPlayers();

        $serviceGameItem = $this->serviceUpdateGameData->getGame();

        $this->assertFalse($this->serviceUpdateGameData->hasGameChanged());
        $this->assertEquals("1", $serviceGameItem->players);
    }

    public function testUpdateNoOfPlayersDefaultEshopValues()
    {
        $gameTitle = 'Mario Kart 8 Deluxe';
        $game = new Game;
        $game->title = $gameTitle;

        $eshopItem = new EshopEuropeGame;
        $eshopItem->players_from = "1";
        $eshopItem->players_to = "1";

        $this->serviceUpdateGameData->setGame($game);
        $this->serviceUpdateGameData->setEshopItem($eshopItem);
        $this->serviceUpdateGameData->updateNoOfPlayers();

        $serviceGameItem = $this->serviceUpdateGameData->getGame();

        $this->assertTrue($this->serviceUpdateGameData->hasGameChanged());
        $this->assertEquals("1", $serviceGameItem->players);
    }

    public function testUpdateNoOfPlayersDefaultEshopValuesNoChange()
    {
        $gameTitle = 'Mario Kart 8 Deluxe';
        $game = new Game;
        $game->title = $gameTitle;
        $game->players = "1";

        $eshopItem = new EshopEuropeGame;
        $eshopItem->players_from = "1";
        $eshopItem->players_to = "1";

        $this->serviceUpdateGameData->setGame($game);
        $this->serviceUpdateGameData->setEshopItem($eshopItem);
        $this->serviceUpdateGameData->updateNoOfPlayers();

        $serviceGameItem = $this->serviceUpdateGameData->getGame();

        $this->assertFalse($this->serviceUpdateGameData->hasGameChanged());
        $this->assertEquals("1", $serviceGameItem->players);
    }

    public function testUpdateNoOfPlayersFromTo()
    {
        $gameTitle = 'Mario Kart 8 Deluxe';
        $game = new Game;
        $game->title = $gameTitle;

        $eshopItem = new EshopEuropeGame;
        $eshopItem->players_from = "1";
        $eshopItem->players_to = "4";

        $this->serviceUpdateGameData->setGame($game);
        $this->serviceUpdateGameData->setEshopItem($eshopItem);
        $this->serviceUpdateGameData->updateNoOfPlayers();

        $serviceGameItem = $this->serviceUpdateGameData->getGame();

        $this->assertTrue($this->serviceUpdateGameData->hasGameChanged());
        $this->assertEquals("1-4", $serviceGameItem->players);
    }

    public function testUpdateNoOfPlayersFromToNoChange()
    {
        $gameTitle = 'Mario Kart 8 Deluxe';
        $game = new Game;
        $game->title = $gameTitle;
        $game->players = "1-4";

        $eshopItem = new EshopEuropeGame;
        $eshopItem->players_from = "2";
        $eshopItem->players_to = "8";

        $this->serviceUpdateGameData->setGame($game);
        $this->serviceUpdateGameData->setEshopItem($eshopItem);
        $this->serviceUpdateGameData->updateNoOfPlayers();

        $serviceGameItem = $this->serviceUpdateGameData->getGame();

        $this->assertFalse($this->serviceUpdateGameData->hasGameChanged());
        $this->assertEquals("1-4", $serviceGameItem->players);
    }

}
