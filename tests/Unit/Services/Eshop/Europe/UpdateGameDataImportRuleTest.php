<?php

namespace Tests\Unit\Services\Eshop;

use App\EshopEuropeGame;
use App\Game;
use App\GameImportRuleEshop;
use App\GameReleaseDate;
use App\Services\Eshop\Europe\UpdateGameData;

use Tests\TestCase;

class UpdateGameDataImportRuleTest extends TestCase
{
    public function testIgnorePlayers()
    {
        $game = new Game(['title' => 'Test players', 'players' => '1']);
        $eshopItem = new EshopEuropeGame(['players_from' => '1', 'players_to' => '4']);
        $gameImportRule = new GameImportRuleEshop(['ignore_players' => '1']);

        $serviceUpdateGameData = new UpdateGameData();
        $serviceUpdateGameData->setGame($game);
        $serviceUpdateGameData->setEshopItem($eshopItem);
        $serviceUpdateGameData->setGameImportRule($gameImportRule);
        $serviceUpdateGameData->updateNoOfPlayers();

        $serviceGameItem = $serviceUpdateGameData->getGame();

        $this->assertEquals('1', $serviceGameItem->players);

        $this->assertEquals(null, $serviceUpdateGameData->getLogMessage());
    }

    public function testIgnorePublisher()
    {
        $game = new Game(['title' => 'Test publisher', 'publisher' => 'ABC']);
        $eshopItem = new EshopEuropeGame(['publisher' => 'The Non-Existent Company']);
        $gameImportRule = new GameImportRuleEshop(['ignore_publishers' => '1']);

        $serviceUpdateGameData = new UpdateGameData();
        $serviceUpdateGameData->setGame($game);
        $serviceUpdateGameData->setEshopItem($eshopItem);
        $serviceUpdateGameData->setGameImportRule($gameImportRule);
        $serviceUpdateGameData->updatePublisher();

        $serviceGameItem = $serviceUpdateGameData->getGame();

        $this->assertEquals('ABC', $serviceGameItem->publisher);

        $this->assertEquals(null, $serviceUpdateGameData->getLogMessage());
    }

    public function testIgnorePrice()
    {
        $game = new Game(['title' => 'Test price', 'price_eshop' => '10.99']);
        $eshopItem = new EshopEuropeGame(['price_regular_f' => '15.99']);
        $gameImportRule = new GameImportRuleEshop(['ignore_price' => '1']);

        $serviceUpdateGameData = new UpdateGameData();
        $serviceUpdateGameData->setGame($game);
        $serviceUpdateGameData->setEshopItem($eshopItem);
        $serviceUpdateGameData->setGameImportRule($gameImportRule);
        $serviceUpdateGameData->updatePrice();

        $serviceGameItem = $serviceUpdateGameData->getGame();

        $this->assertEquals('10.99', $serviceGameItem->price_eshop);

        $this->assertEquals(null, $serviceUpdateGameData->getLogMessage());
    }

    public function testIgnoreReleaseDate()
    {
        $game = new Game([
            'title' => 'Test release date',
            'eu_release_date' => '2020-12-31',
        ]);
        $eshopItem = new EshopEuropeGame(['pretty_date_s' => '15/06/2022']);
        $gameImportRule = new GameImportRuleEshop(['ignore_europe_dates' => '1']);

        $serviceUpdateGameData = new UpdateGameData();
        $serviceUpdateGameData->setGame($game);
        $serviceUpdateGameData->setEshopItem($eshopItem);
        $serviceUpdateGameData->setGameImportRule($gameImportRule);
        $serviceUpdateGameData->updateReleaseDate();

        $serviceGameItem = $serviceUpdateGameData->getGame();

        $this->assertEquals('2020-12-31', $serviceGameItem->eu_release_date);

        $this->assertEquals(null, $serviceUpdateGameData->getLogMessage());
    }

    // *** Cannot test genres as it's coupled to the DB

}
