<?php

namespace Tests\Unit\Domain\WeeklyBatch;

use App\Domain\WeeklyBatch\RawTextParser;
use Tests\TestCase;

class RawTextParserTest extends TestCase
{
    private RawTextParser $parser;

    public function setUp(): void
    {
        $this->parser = new RawTextParser();
        parent::setUp();
    }

    public function tearDown(): void
    {
        unset($this->parser);
        parent::tearDown();
    }

    // ---- Standard block: console, date, genres, sale price, description ----

    public function testStandardBlock()
    {
        $raw = "The Mermaid Mask\nThe Mermaid Mask\n\n"
            . "Nintendo Switch 2 • 16/07/2026 • Adventure, Other, Puzzle\n\n"
            . "£17.75£15.97*\n\n"
            . "An impossible locked-room murder.\n";

        $entries = $this->parser->parse($raw);

        $this->assertCount(1, $entries);
        $this->assertEquals('The Mermaid Mask', $entries[0]['title_raw']);
        $this->assertEquals('16/07/2026', $entries[0]['release_date_raw']);
        $this->assertEquals('Adventure, Other, Puzzle', $entries[0]['nintendo_genres']);
        $this->assertEquals(17.75, $entries[0]['price_gbp']);
    }

    // ---- A meta line with no genres must still parse (was silently dropped) ----

    public function testMetaLineWithoutGenres()
    {
        $raw = "Heave Ho 2\nHeave Ho 2\n\n"
            . "Nintendo Switch 2 • 16/07/2026\n\n"
            . "£8.99*\n\n"
            . "Get a grip, it's Heave Ho 2!\n";

        $entries = $this->parser->parse($raw);

        $this->assertCount(1, $entries);
        $this->assertEquals('Heave Ho 2', $entries[0]['title_raw']);
        $this->assertEquals('16/07/2026', $entries[0]['release_date_raw']);
        $this->assertEquals('', $entries[0]['nintendo_genres']);
    }

    // ---- A dual-console meta line must parse (was silently dropped) ----

    public function testDualConsoleMetaLine()
    {
        $raw = "STARBITES\nSTARBITES\n\n"
            . "Nintendo Switch, Nintendo Switch 2 • 14/07/2026 • RPG\n\n"
            . "Starting from: £4.49*\n\n"
            . "Break the barriers.\n";

        $entries = $this->parser->parse($raw);

        $this->assertCount(1, $entries);
        $this->assertEquals('STARBITES', $entries[0]['title_raw']);
        $this->assertEquals('RPG', $entries[0]['nintendo_genres']);
    }

    // ---- "Demo available" lines are skipped without swallowing the next game ----

    public function testDemoAvailableLineDoesNotDropNextGame()
    {
        $raw = "Heave Ho 2\nHeave Ho 2\n\n"
            . "Nintendo Switch 2 • 16/07/2026 • Party\n\n"
            . "£8.99*\n\n"
            . "Get a grip.\n\n"
            . "Demo available\n"
            . "STAR OCEAN\nSTAR OCEAN\n\n"
            . "Nintendo Switch 2 • 16/07/2026 • Action, RPG\n\n"
            . "£44.99*\n\n"
            . "Two worlds.\n";

        $entries = $this->parser->parse($raw);

        $this->assertCount(2, $entries);
        $this->assertEquals('Heave Ho 2', $entries[0]['title_raw']);
        $this->assertEquals('STAR OCEAN', $entries[1]['title_raw']);
    }

    // ---- Independent block count matches parsed count across mixed formats ----

    public function testCountGameBlocksMatchesMixedFormats()
    {
        $raw = "The Mermaid Mask\nThe Mermaid Mask\n\n"
            . "Nintendo Switch 2 • 16/07/2026 • Adventure\n\n£17.75*\n\nMurder.\n\n"
            . "Heave Ho 2\nHeave Ho 2\n\n"
            . "Nintendo Switch 2 • 16/07/2026\n\n£8.99*\n\nGrip.\n\n"
            . "STARBITES\nSTARBITES\n\n"
            . "Nintendo Switch, Nintendo Switch 2 • 14/07/2026 • RPG\n\nStarting from: £4.49*\n\nBitter.\n";

        $entries = $this->parser->parse($raw);

        $this->assertEquals(3, $this->parser->countGameBlocks($raw));
        $this->assertCount(3, $entries);
    }

    // ---- Titles with a leading space still pair for the block count ----

    public function testCountGameBlocksHandlesLeadingWhitespaceTitles()
    {
        $raw = " Decollate Decoration\nDecollate Decoration\n\n"
            . "Nintendo Switch 2 • 16/07/2026 • Adventure\n\n£8.09£5.66*\n\nHaunting.\n";

        $this->assertEquals(1, $this->parser->countGameBlocks($raw));
        $this->assertCount(1, $this->parser->parse($raw));
    }
}
