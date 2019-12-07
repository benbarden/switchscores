<?php

namespace Tests\Unit\Services\Game;

use Tests\TestCase;
use Illuminate\Support\Collection;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\Services\Game\TitleMatch;
use App\Game;

class TitleMatchTest extends TestCase
{
    /**
     * @var TitleMatch
     */
    private $gameTitleMatch;

    public function setUp(): void
    {
        parent::setUp();
        $this->gameTitleMatch = new TitleMatch();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->gameTitleMatch);
    }

    public function testSetMatchRule()
    {
        $matchRule = "Review";
        $this->gameTitleMatch->setMatchRule($matchRule);
        $this->assertEquals($matchRule, $this->gameTitleMatch->getMatchRule());
    }

    public function testPrepareMatchRuleModifyPrefix()
    {
        $matchRule = "Review$/";
        $expected = "/^Review$/";
        $this->gameTitleMatch->setMatchRule($matchRule);
        $this->gameTitleMatch->prepareMatchRule();
        $this->assertEquals($expected, $this->gameTitleMatch->getMatchRule());
    }

    public function testPrepareMatchRuleModifySuffix()
    {
        $matchRule = "/^Review";
        $expected = "/^Review$/";
        $this->gameTitleMatch->setMatchRule($matchRule);
        $this->gameTitleMatch->prepareMatchRule();
        $this->assertEquals($expected, $this->gameTitleMatch->getMatchRule());
    }

    public function testPrepareMatchRuleModifyBoth()
    {
        $matchRule = "Review";
        $expected = "/^Review$/";
        $this->gameTitleMatch->setMatchRule($matchRule);
        $this->gameTitleMatch->prepareMatchRule();
        $this->assertEquals($expected, $this->gameTitleMatch->getMatchRule());
    }

    public function testPrepareMatchRuleNoChange()
    {
        $matchRule = "/^Review: (.*) - (.*)$/";
        $expected = "/^Review: (.*) - (.*)$/";
        $this->gameTitleMatch->setMatchRule($matchRule);
        $this->gameTitleMatch->prepareMatchRule();
        $this->assertEquals($expected, $this->gameTitleMatch->getMatchRule());
    }

    public function testTheFlameInTheFlood()
    {
        $title = 'The Flame in the Flood';
        $matchRule = '';
        $expected = 'The Flame in the Flood';

        $this->gameTitleMatch->setMatchRule($matchRule);
        $actual = $this->gameTitleMatch->generate($title);
        $this->assertEquals($expected, $actual);
    }

    public function testNintendoLifeMagesOfMystralia()
    {
        $title = 'Review: Mages of Mystralia - A Colourful Spellcasting Adventure That Just Falls Short';
        $matchRule = "/^Review: (.*) - (.*)$/";
        $expected = 'Mages of Mystralia';

        $this->gameTitleMatch->setMatchRule($matchRule);
        $this->gameTitleMatch->setMatchIndex(1);
        $actual = $this->gameTitleMatch->generate($title);
        $this->assertEquals($expected, $actual);
    }

    public function testCubedBlazBlue()
    {
        $title = 'BlazBlue: Central Fiction Special Edition (Nintendo Switch)';
        $matchRule = "/^(.*) \(Nintendo Switch\)$/";
        $expected = 'BlazBlue: Central Fiction Special Edition';

        $this->gameTitleMatch->setMatchRule($matchRule);
        $this->gameTitleMatch->setMatchIndex(1);
        $actual = $this->gameTitleMatch->generate($title);
        $this->assertEquals($expected, $actual);
    }

    public function testMiketendoUnrulyHeroes()
    {
        $title = '[Review] Unruly Heroes (Nintendo Switch)';
        $matchRule = "/^\[Review\] (.*) \(Nintendo Switch\)$/";
        $expected = 'Unruly Heroes';

        $this->gameTitleMatch->setMatchRule($matchRule);
        $this->gameTitleMatch->setMatchIndex(1);
        $actual = $this->gameTitleMatch->generate($title);
        $this->assertEquals($expected, $actual);
    }

    public function testNindieSpotlightAirheart()
    {
        $title = 'Review: Airheart - Tales of Broken Wings [Nintendo Switch eShop]';
        $matchRule = "/^Review: (.*) \[Nintendo Switch eShop\]$/";
        $expected = 'Airheart - Tales of Broken Wings';

        $this->gameTitleMatch->setMatchRule($matchRule);
        $this->gameTitleMatch->setMatchIndex(1);
        $actual = $this->gameTitleMatch->generate($title);
        $this->assertEquals($expected, $actual);
    }

    public function testNintendoInsiderBigCrownShowdown()
    {
        $title = 'Big Crown Showdown Review';
        $matchRule = "/^(.*) Review$/";
        $expected = 'Big Crown Showdown';

        $this->gameTitleMatch->setMatchRule($matchRule);
        $this->gameTitleMatch->setMatchIndex(1);
        $actual = $this->gameTitleMatch->generate($title);
        $this->assertEquals($expected, $actual);
    }

    public function testPureNintendoBuildABridge()
    {
        $title = 'Review: Build a Bridge! (Nintendo Switch)';
        $matchRule = "/^Review: (.*) \(Nintendo Switch\)$/";
        $expected = 'Build a Bridge!';

        $this->gameTitleMatch->setMatchRule($matchRule);
        $this->gameTitleMatch->setMatchIndex(1);
        $actual = $this->gameTitleMatch->generate($title);
        $this->assertEquals($expected, $actual);
    }

    public function testRapidReviewsUkClockSimulatorWithRule()
    {
        $title = 'Clock Simulator';
        $matchRule = "/^(.*)$/";
        $expected = 'Clock Simulator';

        $this->gameTitleMatch->setMatchRule($matchRule);
        $this->gameTitleMatch->setMatchIndex(1);
        $actual = $this->gameTitleMatch->generate($title);
        $this->assertEquals($expected, $actual);
    }

    public function testRapidReviewsUkClockSimulatorEmptyRule()
    {
        $title = 'Clock Simulator';
        $matchRule = "";
        $expected = 'Clock Simulator';

        $this->gameTitleMatch->setMatchRule($matchRule);
        $this->gameTitleMatch->setMatchIndex(1);
        $actual = $this->gameTitleMatch->generate($title);
        $this->assertEquals($expected, $actual);
    }

    public function testSwitchPlayerAtelierMeruru()
    {
        $title = 'Atelier Meruru: The Apprentice of Arland DX Review';
        $matchRule = "/^(.*) Review$/";
        $expected = 'Atelier Meruru: The Apprentice of Arland DX';

        $this->gameTitleMatch->setMatchRule($matchRule);
        $this->gameTitleMatch->setMatchIndex(1);
        $actual = $this->gameTitleMatch->generate($title);
        $this->assertEquals($expected, $actual);
    }

    public function testSwitchWatchFirst()
    {
        $title = 'Pikuniku Nintendo Switch Review';
        //$matchRule = "/^(.*) Nintendo Switch Review$/";
        $matchRule = "/^(.*?) ((Nintendo Switch Review)|(Switch Review)|(Review)|(Review &#8211;))/";
        $expected = 'Pikuniku';

        $this->gameTitleMatch->setMatchRule($matchRule);
        $this->gameTitleMatch->setMatchIndex(1);
        $actual = $this->gameTitleMatch->generate($title);
        $this->assertEquals($expected, $actual);
    }

    public function testSwitchWatchSecond()
    {
        $title = 'Odium to the Core Switch Review';
        //$matchRule = "/^(.*) Switch Review$/";
        $matchRule = "/^(.*?) ((Nintendo Switch Review)|(Switch Review)|(Review)|(Review &#8211;))/";
        $expected = 'Odium to the Core';

        $this->gameTitleMatch->setMatchRule($matchRule);
        $this->gameTitleMatch->setMatchIndex(1);
        $actual = $this->gameTitleMatch->generate($title);
        $this->assertEquals($expected, $actual);
    }

    public function testSwitchWatchThird()
    {
        $title = 'Holy Potatoes! We&#8217;re In Space?! Review';
        //$matchRule = "/^(.*) Review$/";
        $matchRule = "/^(.*?) ((Nintendo Switch Review)|(Switch Review)|(Review)|(Review &#8211;))/";
        $expected = 'Holy Potatoes! We&#8217;re In Space?!';

        $this->gameTitleMatch->setMatchRule($matchRule);
        $this->gameTitleMatch->setMatchIndex(1);
        $actual = $this->gameTitleMatch->generate($title);
        $this->assertEquals($expected, $actual);
    }

    public function testSwitchWatchFourth()
    {
        $title = 'Achtung! Cthulhu Tactics Review &#8211; WW2 XCOM on Switch?';
        //$matchRule = "/^(.*) Review &#8211; (.*)$/";
        $matchRule = "/^(.*?) ((Nintendo Switch Review)|(Switch Review)|(Review)|(Review &#8211;))/";
        $expected = 'Achtung! Cthulhu Tactics';

        $this->gameTitleMatch->setMatchRule($matchRule);
        $this->gameTitleMatch->setMatchIndex(1);
        $actual = $this->gameTitleMatch->generate($title);
        $this->assertEquals($expected, $actual);
    }

    public function testTheSwitchEffectMyMemoryOfUs()
    {
        $title = '[Review] My Memory of Us &#8211; Nintendo Switch';
        $matchRule = "/^\[Review\] (.*) &#8211; Nintendo Switch$/";
        $expected = 'My Memory of Us';

        $this->gameTitleMatch->setMatchRule($matchRule);
        $this->gameTitleMatch->setMatchIndex(1);
        $actual = $this->gameTitleMatch->generate($title);
        $this->assertEquals($expected, $actual);
    }

    public function testTwoBeardGamingFightOfGods()
    {
        $title = 'Fight of Gods &#8211; Nintendo Switch';
        $matchRule = "/^(.*) &#8211; Nintendo Switch$/";
        $expected = 'Fight of Gods';

        $this->gameTitleMatch->setMatchRule($matchRule);
        $this->gameTitleMatch->setMatchIndex(1);
        $actual = $this->gameTitleMatch->generate($title);
        $this->assertEquals($expected, $actual);
    }

    public function testVideoChumsTwinkleStarSprites()
    {
        $title = 'ACA NeoGeo: Twinkle Star Sprites Review';
        $matchRule = "/^(.*) Review$/";
        $expected = 'ACA NeoGeo: Twinkle Star Sprites';

        $this->gameTitleMatch->setMatchRule($matchRule);
        $this->gameTitleMatch->setMatchIndex(1);
        $actual = $this->gameTitleMatch->generate($title);
        $this->assertEquals($expected, $actual);
    }

    public function testNintendadTravisStrikesAgain()
    {
        $title = '[Review] Travis Strikes Again &#8211; Nintendo Switch';
        $matchRule = "/^\[Review\] (.*) (&#8211;|–) Nintendo Switch$/";
        $expected = 'Travis Strikes Again';

        $this->gameTitleMatch->setMatchRule($matchRule);
        $this->gameTitleMatch->setMatchIndex(1);
        $actual = $this->gameTitleMatch->generate($title);
        $this->assertEquals($expected, $actual);
    }

    public function testNintendadTheRaven()
    {
        $title = '[Review] The Raven Remastered – Nintendo Switch';
        $matchRule = "/^\[Review\] (.*) (&#8211;|–) Nintendo Switch$/";
        $expected = 'The Raven Remastered';

        $this->gameTitleMatch->setMatchRule($matchRule);
        $this->gameTitleMatch->setMatchIndex(1);
        $actual = $this->gameTitleMatch->generate($title);
        $this->assertEquals($expected, $actual);
    }

    public function testJpsSwitchmaniaSelmaAndTheWisp()
    {
        $title = 'Game Review #312: Selma and the Wisp (Nintendo Switch)';
        $matchRule = "/^Game Review #(.*): (.*) \(Nintendo Switch\)$/";
        $expected = 'Selma and the Wisp';

        $this->gameTitleMatch->setMatchRule($matchRule);
        $this->gameTitleMatch->setMatchIndex(2);
        $actual = $this->gameTitleMatch->generate($title);
        $this->assertEquals($expected, $actual);
    }
}
