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

    public function setUp()
    {
        parent::setUp();
        $this->gameTitleMatch = new TitleMatch();
    }

    public function tearDown()
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
}
