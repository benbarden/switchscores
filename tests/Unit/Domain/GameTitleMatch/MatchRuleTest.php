<?php

namespace Tests\Unit\Domain\GameTitleMatch;

use Tests\TestCase;

use App\Domain\GameTitleMatch\MatchRule;

class MatchRuleTest extends TestCase
{
    public function testSimple()
    {
        $pattern = "(.*)";
        $index = 0;
        $matchRule = new MatchRule($pattern, $index);

        $expected = "/^(.*)$/";
        $actual = $matchRule->getPattern();

        $this->assertEquals($expected, $actual);
    }

    public function testNLifePattern()
    {
        $pattern = "Review: (.*) - (.*)";
        $index = 1;
        $matchRule = new MatchRule($pattern, $index);

        $expected = "/^Review: (.*) - (.*)$/";
        $actual = $matchRule->getPattern();

        $this->assertEquals($expected, $actual);
    }

    public function testExactMatch()
    {
        $pattern = "(.*)";
        $index = 0;
        $matchRule = new MatchRule($pattern, $index);

        $feedTitle = "Super Mario Odyssey";
        $expected = ["Super Mario Odyssey"];
        $actual = $matchRule->generateMatch($feedTitle);

        $this->assertEquals($expected, $actual);
    }

    public function testRuleMatch()
    {
        $pattern = "(Mini )?Review: (.*) - (.*)";
        $index = 2;
        $matchRule = new MatchRule($pattern, $index);

        $feedTitle = "Review: Super Mario Odyssey - The best Mario game in years";
        $expected = ["Super Mario Odyssey"];
        $actual = $matchRule->generateMatch($feedTitle);

        $this->assertEquals($expected, $actual);
    }

    public function testGetParsedTitle()
    {
        $pattern = "(Mini )?Review: (.*) - (.*)";
        $index = 2;
        $matchRule = new MatchRule($pattern, $index);

        $feedTitle = "Review: Super Mario Odyssey - The best Mario game in years";
        $matchRule->generateMatch($feedTitle);

        $expected = "Super Mario Odyssey";
        $actual = $matchRule->getParsedTitle();

        $this->assertEquals($expected, $actual);
    }

    public function testCurlyQuotes()
    {
        //$pattern = "Review: (.*) - (.*)";
        //$index = 1;
        $pattern = "(Mini )?Review: (.*) - (.*)";
        $index = 2;
        $matchRule = new MatchRule($pattern, $index);

        $feedTitle = "Review: Y’s Origin - Ys-y does it";
        $expected = ["Y’s Origin", "Y's Origin"];
        $actual = $matchRule->generateMatch($feedTitle);

        $this->assertEquals($expected, $actual);
    }

    public function testMiniReviewCloudGardens()
    {
        $feedTitle = "Mini Review: Cloud Gardens - A Low-Key, Rich, And Satisfyingly 'Chill' Game";

        $pattern = "(Mini )?Review: (.*) - (.*)";
        $index = 2;
        $matchRule = new MatchRule($pattern, $index);

        $expected = ["Cloud Gardens"];
        $actual = $matchRule->generateMatch($feedTitle);

        $this->assertEquals($expected, $actual);
    }
}
