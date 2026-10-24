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

    // ---- Games further ahead: year-only date, TBD, no price (#210) ----

    public function testYearOnlyAndTbdRowsComeThroughUndated()
    {
        // Real shapes from the 2026-10-24 Switch 2 Upcoming paste
        $raw = "Metroid Ravenous\nMetroid Ravenous\n\n"
            . "Nintendo Switch 2 • 28/01/2027 • Action, Adventure, Shooter\n\n£49.99*\n\n"
            . "The latest game in the 2D Metroid series, is coming to Nintendo Switch 2.\n\n"
            . "Pikuniku 2\nPikuniku 2\n\n"
            . "Nintendo Switch 2 • 2027 • Adventure, Platformer, Puzzle\n\n"
            . "Absurd puzzle-exploration adventure Pikuniku 2 brings the whimsy!\n\n"
            . "Enter the Gungeon 2\nEnter the Gungeon 2\n\n"
            . "Nintendo Switch 2 • Release date: TBD • Action, Adventure\n\n"
            . "Experience the reloaded sequel to the iconic bullet dungeon crawler on Nintendo Switch 2.\n";

        $entries = $this->parser->parse($raw);

        $this->assertEquals(3, $this->parser->countGameBlocks($raw));
        $this->assertCount(3, $entries);

        $this->assertEquals('2027-01-28', $entries[0]['release_date']);
        $this->assertEquals(49.99, $entries[0]['price_gbp']);

        $this->assertEquals('Pikuniku 2', $entries[1]['title_raw']);
        $this->assertNull($entries[1]['release_date']);
        $this->assertEquals('Adventure, Platformer, Puzzle', $entries[1]['nintendo_genres']);
        // No price yet: the next line is the description, not a price
        $this->assertNull($entries[1]['price_gbp']);
        $this->assertEquals('price missing', $entries[1]['price_flag_reason']);
        $this->assertStringStartsWith('Absurd puzzle-exploration', $entries[1]['description']);

        $this->assertEquals('Enter the Gungeon 2', $entries[2]['title_raw']);
        $this->assertNull($entries[2]['release_date']);
        $this->assertEquals('Action, Adventure', $entries[2]['nintendo_genres']);
    }
}
