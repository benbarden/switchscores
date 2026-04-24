<?php

namespace Tests\Unit\Domain\WeeklyBatch;

use App\Domain\WeeklyBatch\TitleNormaliser;
use Tests\TestCase;

class TitleNormaliserTest extends TestCase
{
    private TitleNormaliser $normaliser;

    public function setUp(): void
    {
        $this->normaliser = new TitleNormaliser();
        parent::setUp();
    }

    public function tearDown(): void
    {
        unset($this->normaliser);
        parent::tearDown();
    }

    // ---- ALL CAPS titles ----

    public function testAllCapsTitle()
    {
        $this->assertEquals(
            'Attention is All You Need',
            $this->normaliser->normalise('ATTENTION IS ALL YOU NEED')
        );
    }

    public function testAllCapsTitleWithMinorWords()
    {
        $this->assertEquals(
            'Face the Relentless Waves',
            $this->normaliser->normalise('FACE THE RELENTLESS WAVES')
        );
    }

    public function testAllCapsTitleWithColon()
    {
        $this->assertEquals(
            'Console Archives Ninja Gaiden III: The Ancient Ship of Doom',
            $this->normaliser->normalise('Console Archives NINJA GAIDEN III: THE ANCIENT SHIP OF DOOM')
        );
    }

    // ---- Short ALL CAPS acronyms should be preserved ----

    public function testShortAcronymPreserved()
    {
        $this->assertEquals('ZPF', $this->normaliser->normalise('ZPF'));
    }

    public function testShortAcronymInMixedTitle()
    {
        $this->assertEquals(
            'ZPF: A Game',
            $this->normaliser->normalise('ZPF: A Game')
        );
    }

    // ---- Console/platform names ----

    public function testMsx2Preserved()
    {
        $this->assertEquals(
            'Egg Console Psycho World MSX2',
            $this->normaliser->normalise('Egg Console Psycho World MSX2')
        );
    }

    public function testEggConsoleNormalisation()
    {
        $this->assertEquals(
            'Egg Console Psycho World MSX2',
            $this->normaliser->normalise('EGGCONSOLE PSYCHO WORLD MSX2')
        );
    }

    // ---- Minor words ----

    public function testItIsLowercasedMidTitle()
    {
        $this->assertEquals(
            'Smash it Wild',
            $this->normaliser->normalise('Smash It Wild')
        );
    }

    public function testMinorWordsLowercased()
    {
        $this->assertEquals(
            'Jay and Silent Bob',
            $this->normaliser->normalise('Jay And Silent Bob')
        );
    }

    public function testMinorWordCapitalisedAtStart()
    {
        $this->assertEquals(
            'The Ancient Ship of Doom',
            $this->normaliser->normalise('THE ANCIENT SHIP OF DOOM')
        );
    }

    public function testMinorWordCapitalisedAfterColon()
    {
        $this->assertEquals(
            'Something: The Return',
            $this->normaliser->normalise('SOMETHING: THE RETURN')
        );
    }

    // ---- Hyphenated ALL CAPS words ----

    public function testHyphenatedAllCapsWordTitleCasedPerPart()
    {
        $this->assertEquals(
            'R-Type DX: Music Encore',
            $this->normaliser->normalise('R-TYPE DX: Music Encore')
        );
    }

    // ---- Mixed-case titles with short ALL CAPS real words ----

    public function testShortAllCapsRealWordsInMixedTitle()
    {
        $this->assertEquals(
            'Get Fit: Power Workout',
            $this->normaliser->normalise('GET FIT – Power Workout')
        );
    }

    // ---- Curly quote normalisation ----

    public function testCurlyDoubleQuotesNormalised()
    {
        $this->assertEquals(
            'Your "Hidden Side" Test',
            $this->normaliser->normalise("Your \u{201C}Hidden Side\u{201D} Test")
        );
    }

    public function testCurlyApostropheNormalised()
    {
        $this->assertEquals(
            "Don't Stop",
            $this->normaliser->normalise("Don\u{2019}t Stop")
        );
    }

    // ---- Trademark/symbol removal ----

    public function testTrademarkRemoved()
    {
        $this->assertEquals('Game Title', $this->normaliser->normalise('Game Title™'));
    }

    public function testRegisteredRemoved()
    {
        $this->assertEquals('Game Title', $this->normaliser->normalise('Game Title®'));
    }

    public function testTrademarkBetweenWordAndDigitInsertsSpace()
    {
        $this->assertEquals('MotoGP 26', $this->normaliser->normalise('MotoGP™26'));
    }

    // ---- Separators ----

    public function testTildeAsSubtitleSeparator()
    {
        $this->assertEquals(
            'Game Title: Subtitle',
            $this->normaliser->normalise('Game Title ~Subtitle~')
        );
    }

    public function testHyphenAsSubtitleSeparator()
    {
        $this->assertEquals(
            'Game Title: Subtitle',
            $this->normaliser->normalise('Game Title - Subtitle')
        );
    }

    public function testHyphenSubtitleWrapped()
    {
        $this->assertEquals(
            'Game Title: Subtitle',
            $this->normaliser->normalise('Game Title -Subtitle-')
        );
    }

    // ---- Survivor roguelike phrases ----

    public function testSurvivorKeywordInTitle()
    {
        $this->assertEquals(
            'Monster Rush Survivors',
            $this->normaliser->normalise('Monster Rush Survivors')
        );
    }

    // ---- EGGCONSOLE normalisation ----

    public function testEggconsolePrefix()
    {
        $this->assertEquals(
            'Egg Console Adventure',
            $this->normaliser->normalise('EGGCONSOLE Adventure')
        );
    }

    // ---- Ampersand ----

    public function testAmpersandToAnd()
    {
        $this->assertEquals(
            'Card and Casino',
            $this->normaliser->normalise('CARD&CASINO')
        );
    }
}
