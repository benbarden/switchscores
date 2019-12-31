<?php

namespace Tests\Unit\Construction\GameImportRule;

use Tests\TestCase;

use App\Construction\GameImportRule\WikipediaBuilder;

class WikipediaBuilderTest extends TestCase
{
    public function testSetGameId()
    {
        $gameId = 1001;

        $importRuleBuilder = new WikipediaBuilder();
        $importRuleBuilder->setGameId($gameId);
        $this->assertEquals($gameId, $importRuleBuilder->getGameId());
    }

    public function testSetIgnoreDevelopers()
    {
        $importRuleBuilder = new WikipediaBuilder();

        $importRuleBuilder->setIgnoreDevelopers(0);
        $this->assertEquals(0, $importRuleBuilder->getGameImportRule()->ignore_developers);

        $importRuleBuilder->setIgnoreDevelopers(1);
        $this->assertEquals(1, $importRuleBuilder->getGameImportRule()->ignore_developers);

        $importRuleBuilder->setIgnoreDevelopers('on');
        $this->assertEquals(1, $importRuleBuilder->getGameImportRule()->ignore_developers);
    }

    public function testSetIgnorePublishers()
    {
        $importRuleBuilder = new WikipediaBuilder();

        $importRuleBuilder->setIgnorePublishers(0);
        $this->assertEquals(0, $importRuleBuilder->getGameImportRule()->ignore_publishers);

        $importRuleBuilder->setIgnorePublishers(1);
        $this->assertEquals(1, $importRuleBuilder->getGameImportRule()->ignore_publishers);

        $importRuleBuilder->setIgnorePublishers('on');
        $this->assertEquals(1, $importRuleBuilder->getGameImportRule()->ignore_publishers);
    }

    public function testSetIgnoreEuropeDates()
    {
        $importRuleBuilder = new WikipediaBuilder();

        $importRuleBuilder->setIgnoreEuropeDates(0);
        $this->assertEquals(0, $importRuleBuilder->getGameImportRule()->ignore_europe_dates);

        $importRuleBuilder->setIgnoreEuropeDates(1);
        $this->assertEquals(1, $importRuleBuilder->getGameImportRule()->ignore_europe_dates);

        $importRuleBuilder->setIgnoreEuropeDates('on');
        $this->assertEquals(1, $importRuleBuilder->getGameImportRule()->ignore_europe_dates);
    }

    public function testSetIgnoreUSDates()
    {
        $importRuleBuilder = new WikipediaBuilder();

        $importRuleBuilder->setIgnoreUSDates(0);
        $this->assertEquals(0, $importRuleBuilder->getGameImportRule()->ignore_us_dates);

        $importRuleBuilder->setIgnoreUSDates(1);
        $this->assertEquals(1, $importRuleBuilder->getGameImportRule()->ignore_us_dates);

        $importRuleBuilder->setIgnoreUSDates('on');
        $this->assertEquals(1, $importRuleBuilder->getGameImportRule()->ignore_us_dates);
    }

    public function testSetIgnoreJapanDates()
    {
        $importRuleBuilder = new WikipediaBuilder();

        $importRuleBuilder->setIgnoreJPDates(0);
        $this->assertEquals(0, $importRuleBuilder->getGameImportRule()->ignore_jp_dates);

        $importRuleBuilder->setIgnoreJPDates(1);
        $this->assertEquals(1, $importRuleBuilder->getGameImportRule()->ignore_jp_dates);

        $importRuleBuilder->setIgnoreJPDates('on');
        $this->assertEquals(1, $importRuleBuilder->getGameImportRule()->ignore_jp_dates);
    }
}
