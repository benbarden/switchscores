<?php

namespace Tests\Unit\Construction\GameImportRule;

use Tests\TestCase;

use App\Construction\GameImportRule\EshopBuilder as Builder;

class EshopBuilderTest extends TestCase
{
    public function testSetGameId()
    {
        $gameId = 1001;

        $importRuleBuilder = new Builder();
        $importRuleBuilder->setGameId($gameId);
        $this->assertEquals($gameId, $importRuleBuilder->getGameId());
    }

    public function testSetIgnorePublishers()
    {
        $importRuleBuilder = new Builder();

        $importRuleBuilder->setIgnorePublishers(0);
        $this->assertEquals(0, $importRuleBuilder->getGameImportRule()->ignore_publishers);

        $importRuleBuilder->setIgnorePublishers(1);
        $this->assertEquals(1, $importRuleBuilder->getGameImportRule()->ignore_publishers);

        $importRuleBuilder->setIgnorePublishers('on');
        $this->assertEquals(1, $importRuleBuilder->getGameImportRule()->ignore_publishers);
    }

    public function testSetIgnoreEuropeDates()
    {
        $importRuleBuilder = new Builder();

        $importRuleBuilder->setIgnoreEuropeDates(0);
        $this->assertEquals(0, $importRuleBuilder->getGameImportRule()->ignore_europe_dates);

        $importRuleBuilder->setIgnoreEuropeDates(1);
        $this->assertEquals(1, $importRuleBuilder->getGameImportRule()->ignore_europe_dates);

        $importRuleBuilder->setIgnoreEuropeDates('on');
        $this->assertEquals(1, $importRuleBuilder->getGameImportRule()->ignore_europe_dates);
    }

    public function testSetIgnorePrice()
    {
        $importRuleBuilder = new Builder();

        $importRuleBuilder->setIgnorePrice(0);
        $this->assertEquals(0, $importRuleBuilder->getGameImportRule()->ignore_price);

        $importRuleBuilder->setIgnorePrice(1);
        $this->assertEquals(1, $importRuleBuilder->getGameImportRule()->ignore_price);

        $importRuleBuilder->setIgnorePrice('on');
        $this->assertEquals(1, $importRuleBuilder->getGameImportRule()->ignore_price);
    }

    public function testSetIgnorePlayers()
    {
        $importRuleBuilder = new Builder();

        $importRuleBuilder->setIgnorePlayers(0);
        $this->assertEquals(0, $importRuleBuilder->getGameImportRule()->ignore_players);

        $importRuleBuilder->setIgnorePlayers(1);
        $this->assertEquals(1, $importRuleBuilder->getGameImportRule()->ignore_players);

        $importRuleBuilder->setIgnorePlayers('on');
        $this->assertEquals(1, $importRuleBuilder->getGameImportRule()->ignore_players);
    }

    public function testSetIgnoreGenres()
    {
        $importRuleBuilder = new Builder();

        $importRuleBuilder->setIgnoreGenres(0);
        $this->assertEquals(0, $importRuleBuilder->getGameImportRule()->ignore_genres);

        $importRuleBuilder->setIgnoreGenres(1);
        $this->assertEquals(1, $importRuleBuilder->getGameImportRule()->ignore_genres);

        $importRuleBuilder->setIgnoreGenres('on');
        $this->assertEquals(1, $importRuleBuilder->getGameImportRule()->ignore_genres);
    }
}
