<?php

namespace Tests\Unit\Services\Feed;

use App\Models\Partner;
use App\Services\Feed\Parser;
use App\Services\Feed\TitleParser;
use Tests\TestCase;

class ParserTest extends TestCase
{
    /**
     * @var Parser
     */
    private $parser;

    public function setUp(): void
    {
        $titleParser = new TitleParser();
        $this->parser = new Parser($titleParser);

        parent::setUp();
    }

    public function tearDown(): void
    {
        unset($this->parser);

        parent::tearDown();
    }

    public function testSiteIdStorage()
    {
        $siteId = Partner::SITE_SWITCH_PLAYER;

        $this->parser->setSiteId($siteId);

        $this->assertEquals($siteId, $this->parser->getSiteId());
    }

    public function testParseBySiteRulesSwitchPlayer()
    {
        $this->parser->setSiteId(Partner::SITE_SWITCH_PLAYER);
        $this->parser->getTitleParser()->setTitle('Syberia Review');
        $this->parser->parseBySiteRules();
        $this->assertEquals('Syberia', $this->parser->getTitleParser()->getTitle());
    }

    public function testParseBySiteRulesNintendoLifeEshop()
    {
        $this->parser->setSiteId(Partner::SITE_NINTENDO_LIFE);
        $this->parser->getTitleParser()->setTitle('One More Dungeon (Switch eShop) Review');
        $this->parser->parseBySiteRules();
        $this->assertEquals('One More Dungeon', $this->parser->getTitleParser()->getTitle());
    }

    public function testParseBySiteRulesNintendoLifeSwitch()
    {
        $this->parser->setSiteId(Partner::SITE_NINTENDO_LIFE);
        $this->parser->getTitleParser()->setTitle('Party Planet (Nintendo Switch) Review');
        $this->parser->parseBySiteRules();
        $this->assertEquals('Party Planet', $this->parser->getTitleParser()->getTitle());
    }

    public function testParseBySiteRulesPureNintendoMiniReview()
    {
        $this->parser->setSiteId(Partner::SITE_PURE_NINTENDO);
        $this->parser->getTitleParser()->setTitle('Mini-Review: Astro Bears Party (Nintendo Switch)');
        $this->parser->parseBySiteRules();
        $this->assertEquals('Astro Bears Party', $this->parser->getTitleParser()->getTitle());
    }

    public function testParseBySiteRulesPureNintendoReview()
    {
        $this->parser->setSiteId(Partner::SITE_PURE_NINTENDO);
        $this->parser->getTitleParser()->setTitle('Review: Conga Master Party! (Nintendo Switch)');
        $this->parser->parseBySiteRules();
        $this->assertEquals('Conga Master Party!', $this->parser->getTitleParser()->getTitle());
    }

    public function testParseBySiteRulesNintendoInsiderReview()
    {
        $this->parser->setSiteId(Partner::SITE_NINTENDO_INSIDER);
        $this->parser->getTitleParser()->setTitle('This Is The Police Review');
        $this->parser->parseBySiteRules();
        $this->assertEquals('This Is The Police', $this->parser->getTitleParser()->getTitle());
    }

    public function testParseBySiteRulesMiketendo64()
    {
        $this->parser->setSiteId(Partner::SITE_MIKETENDO64);
        $this->parser->getTitleParser()->setTitle('[Review] Phantom Breaker: Battle Grounds Overdrive (Nintendo Switch)');
        $this->parser->parseBySiteRules();
        $this->assertEquals('Phantom Breaker: Battle Grounds Overdrive', $this->parser->getTitleParser()->getTitle());
    }

    public function testParseBySiteRulesNindieSpotlight()
    {
        $this->parser->setSiteId(Partner::SITE_NINDIE_SPOTLIGHT);
        $this->parser->getTitleParser()->setTitle('Review: Never Stop Sneakin\'');
        $this->parser->parseBySiteRules();
        $this->assertEquals('Never Stop Sneakin\'', $this->parser->getTitleParser()->getTitle());
    }

    public function testParseBySiteRulesSwitchWatch()
    {
        $this->parser->setSiteId(Partner::SITE_SWITCHWATCH);
        $this->parser->getTitleParser()->setTitle('Mushroom Wars 2 Nintendo Switch Review');
        $this->parser->parseBySiteRules();
        $this->assertEquals('Mushroom Wars 2', $this->parser->getTitleParser()->getTitle());
    }
}
