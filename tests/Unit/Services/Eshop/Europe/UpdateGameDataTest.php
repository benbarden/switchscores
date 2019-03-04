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

    public function testNintendoPageUrlNull()
    {
        $gameTitle = 'Yoshi\'s Crafted World';
        $game = new Game;
        $game->title = $gameTitle;

        $eshopItemUrl = 'https://nintendo.co.uk/abc';
        $eshopItem = new EshopEuropeGame;
        $eshopItem->url = $eshopItemUrl;

        $this->serviceUpdateGameData->setGame($game);
        $this->serviceUpdateGameData->setEshopItem($eshopItem);
        $this->serviceUpdateGameData->updateNintendoPageUrl();

        $serviceGameItem = $this->serviceUpdateGameData->getGame();

        $this->assertTrue($this->serviceUpdateGameData->hasGameChanged());
        $this->assertEquals($eshopItemUrl, $serviceGameItem->nintendo_page_url);
    }

}
