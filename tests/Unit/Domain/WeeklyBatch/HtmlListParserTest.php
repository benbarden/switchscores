<?php

namespace Tests\Unit\Domain\WeeklyBatch;

use App\Domain\WeeklyBatch\HtmlListParser;
use Tests\TestCase;

class HtmlListParserTest extends TestCase
{
    private HtmlListParser $parser;
    private string $sampleHtml;

    public function setUp(): void
    {
        $this->parser = new HtmlListParser();
        $this->sampleHtml = file_get_contents(__DIR__.'/fixtures/nintendo-sample.html');
        parent::setUp();
    }

    public function tearDown(): void
    {
        unset($this->parser);
        parent::tearDown();
    }

    private function entryByNsuid(array $entries, string $nsuid): array
    {
        foreach ($entries as $e) {
            if ($e['nsuid'] === $nsuid) {
                return $e;
            }
        }
        $this->fail("No entry with nsuid {$nsuid}");
    }

    // ---- Every row is captured; no games silently dropped ----

    public function testAllRowsParsed()
    {
        $entries = $this->parser->parse($this->sampleHtml);

        $this->assertCount(4, $entries);
        $this->assertEquals(4, $this->parser->countGameBlocks($this->sampleHtml));
    }

    public function testLooksLikeHtml()
    {
        $this->assertTrue($this->parser->looksLikeHtml($this->sampleHtml));
        $this->assertFalse($this->parser->looksLikeHtml("The Mermaid Mask\nNintendo Switch 2 • 16/07/2026"));
    }

    // ---- Standout fields: NSUID, store URL, square packshot ----

    public function testCapturesUrlsAndNsuid()
    {
        $entries = $this->parser->parse($this->sampleHtml);
        $mermaid = $this->entryByNsuid($entries, '70010000122454');

        $this->assertEquals('The Mermaid Mask', $mermaid['title_raw']);
        $this->assertEquals(
            'https://www.nintendo.com/en-gb/Games/Nintendo-Switch-2-games/The-Mermaid-Mask-3137751.html',
            $mermaid['nintendo_url']
        );
        $this->assertEquals(
            'https://www.nintendo.com/eu/media/images/assets/nintendo_switch_2_games/themermaidmask/1x1_TheMermaidMask_image500w.jpg',
            $mermaid['packshot_url']
        );
    }

    // ---- Meta: console, date, genres ----

    public function testParsesMetaWithGenres()
    {
        $mermaid = $this->entryByNsuid($this->parser->parse($this->sampleHtml), '70010000122454');

        $this->assertEquals('Nintendo Switch 2', $mermaid['console_raw']);
        $this->assertEquals('16/07/2026', $mermaid['release_date_raw']);
        $this->assertEquals('2026-07-16', $mermaid['release_date']);
        $this->assertEquals('Adventure, Other, Puzzle', $mermaid['nintendo_genres']);
    }

    public function testParsesRowWithoutGenres()
    {
        // Heave Ho 2 — the kind of row the plain-text parser used to drop.
        $heave = $this->entryByNsuid($this->parser->parse($this->sampleHtml), '70010000113972');

        $this->assertEquals('Heave Ho 2', $heave['title_raw']);
        $this->assertEquals('16/07/2026', $heave['release_date_raw']);
        $this->assertEquals('', $heave['nintendo_genres']);
    }

    // ---- Price shapes ----

    public function testDiscountPrice()
    {
        $mermaid = $this->entryByNsuid($this->parser->parse($this->sampleHtml), '70010000122454');

        $this->assertEquals(17.75, $mermaid['price_gbp']); // headline/original price
        $this->assertFalse($mermaid['price_flag']);
        $this->assertStringContainsString('£17.75', $mermaid['price_raw']);
        $this->assertStringContainsString('£15.97', $mermaid['price_raw']);
    }

    public function testStartingFromZeroPrice()
    {
        $outlaws = $this->entryByNsuid($this->parser->parse($this->sampleHtml), '70010000132050');

        $this->assertEquals(0.0, $outlaws['price_gbp']);
        $this->assertTrue($outlaws['price_flag']);
        $this->assertStringContainsString('free-to-play', $outlaws['price_flag_reason']);
    }

    public function testPlainPrice()
    {
        $heave = $this->entryByNsuid($this->parser->parse($this->sampleHtml), '70010000113972');

        $this->assertEquals(8.99, $heave['price_gbp']);
        $this->assertFalse($heave['price_flag']);
        $this->assertEquals('£8.99*', $heave['price_raw']);
    }

    // ---- Flags: demo, download-only ----

    public function testDemoAvailableFlag()
    {
        $entries = $this->parser->parse($this->sampleHtml);

        $this->assertTrue($this->entryByNsuid($entries, '70010000113972')['has_demo']);  // Heave Ho 2
        $this->assertFalse($this->entryByNsuid($entries, '70010000122454')['has_demo']); // Mermaid Mask
    }

    public function testDownloadOnlyFlag()
    {
        $entries = $this->parser->parse($this->sampleHtml);

        // Outlaws has the red-cap (download-only) badge; Mermaid Mask does not.
        $this->assertTrue($this->entryByNsuid($entries, '70010000132050')['is_download_only']);
        $this->assertFalse($this->entryByNsuid($entries, '70010000122454')['is_download_only']);
    }

    // ---- Description ----

    public function testCapturesDescription()
    {
        $star = $this->entryByNsuid($this->parser->parse($this->sampleHtml), '70010000121078');

        $this->assertEquals('TWO WORLDS, ONE FATEFUL ENCOUNTER.', $star['description']);
    }

    // ---- Dual-console row: "Nintendo Switch, Nintendo Switch 2" ----

    public function testDualConsoleRowReadsFullConsoleName()
    {
        $html = file_get_contents(__DIR__.'/fixtures/nintendo-dual-console.html');
        $entries = $this->parser->parse($html);

        $this->assertCount(1, $entries);
        $this->assertEquals('Nintendo Switch, Nintendo Switch 2', $entries[0]['console_raw']);
        $this->assertEquals('RPG', $entries[0]['nintendo_genres']);
        $this->assertNotSame('', $entries[0]['nintendo_url']);
        $this->assertNotSame('', $entries[0]['packshot_url']);
    }

    // ---- Upcoming row with no price element at all ----

    public function testRowWithNoPriceElement()
    {
        $html = file_get_contents(__DIR__.'/fixtures/nintendo-no-price.html');
        $entries = $this->parser->parse($html);

        $this->assertCount(1, $entries);
        $this->assertNull($entries[0]['price_gbp']);
        $this->assertTrue($entries[0]['price_flag']);
        $this->assertEquals('price missing', $entries[0]['price_flag_reason']);
        // The rest of the row still parses fine.
        $this->assertEquals('2026-07-22', $entries[0]['release_date']);
        $this->assertEquals('Nintendo Switch', $entries[0]['console_raw']);
    }

    // ---- Empty input ----

    public function testEmptyInput()
    {
        $this->assertSame([], $this->parser->parse(''));
        $this->assertSame(0, $this->parser->countGameBlocks(''));
    }
}
