<?php

namespace Tests\Unit\Domain\GameGroup;

use App\Domain\GameGroup\TitleMatchKey;
use Tests\TestCase;

/**
 * Cover for the Switch 1 / Switch 2 title match key (#44). The pairs here are real titles
 * from the 2026-09-25 prod dump.
 */
class TitleMatchKeyTest extends TestCase
{
    private function key(string $title): string
    {
        return (new TitleMatchKey())->forTitle($title);
    }

    private function assertSameGame(string $switch2, string $switch1): void
    {
        $this->assertSame($this->key($switch1), $this->key($switch2));
    }

    public function testColonSuffix()
    {
        $this->assertSameGame(
            'The Legend of Zelda: Tears of the Kingdom: Nintendo Switch 2 Edition',
            'The Legend of Zelda: Tears of the Kingdom'
        );
    }

    public function testBareSuffix()
    {
        $this->assertSameGame('Shadow Labyrinth Nintendo Switch 2 Edition', 'Shadow Labyrinth');
    }

    public function testHyphenSuffix()
    {
        $this->assertSameGame('Hades II - Nintendo Switch 2 Edition', 'Hades II');
    }

    public function testBracketSuffix()
    {
        $this->assertSameGame('Some Game (Switch 2)', 'Some Game');
    }

    public function testSuffixIgnoresCase()
    {
        $this->assertSameGame('Some Game: NINTENDO SWITCH 2 EDITION', 'Some Game');
    }

    public function testColonAndHyphenSeparatorsAgree()
    {
        $this->assertSameGame('Test Game - ABC', 'Test Game: ABC');
    }

    public function testCurlyAndStraightApostrophesAgree()
    {
        $this->assertSameGame("Sid Meier\u{2019}s Civilization VII: Nintendo Switch 2 Edition", "Sid Meier's Civilization VII");
    }

    public function testAmpersandAndWordAgree()
    {
        $this->assertSameGame('Dragon Quest I & II HD-2D Remake', 'Dragon Quest I and II HD-2D Remake');
    }

    public function testDragonQuestIAndIIIsNotIII()
    {
        // Stripping all punctuation made these the same key: "dragonquestiiihd2dremake"
        $this->assertNotSame(
            $this->key('Dragon Quest III HD-2D Remake'),
            $this->key('Dragon Quest I & II HD-2D Remake')
        );
    }

    public function testTrademarkSymbolsIgnored()
    {
        $this->assertSameGame('MotoGP™ 26', 'MotoGP 26');
    }

    public function testKeyShape()
    {
        $this->assertSame(
            'romancing saga 2 revenge of the seven',
            $this->key('Romancing SaGa 2: Revenge of the Seven - Nintendo Switch 2 Edition')
        );
    }

    public function testStripConsoleSuffixKeepsCase()
    {
        $this->assertSame(
            'Rune Factory: Guardians of Azuma',
            (new TitleMatchKey())->stripConsoleSuffix('Rune Factory: Guardians of Azuma Nintendo Switch 2 Edition')
        );
    }

    public function testStripConsoleSuffixLeavesOtherTitlesAlone()
    {
        $this->assertSame('Mario Kart World', (new TitleMatchKey())->stripConsoleSuffix('Mario Kart World'));
    }
}
