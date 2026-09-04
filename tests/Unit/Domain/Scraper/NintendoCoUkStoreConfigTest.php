<?php

namespace Tests\Unit\Domain\Scraper;

use App\Domain\Scraper\NintendoCoUkGameData;
use Tests\TestCase;

/**
 * The store page carries an inline JS config block (`nt_data` and `nsuids`) holding the
 * title, genres, release date and NSUID. The weekly batch reads those from the pasted
 * listing instead, so nothing parsed them before; the single-game add tool has no
 * listing, so it reads them here (#135).
 *
 * These are regex matches against markup we do not control, so they are the part most
 * likely to go quietly null after a Nintendo redesign.
 */
class NintendoCoUkStoreConfigTest extends TestCase
{
    private function pageWith(string $script): NintendoCoUkGameData
    {
        return new NintendoCoUkGameData('<html><head><title>x</title></head><body>'.$script.'</body></html>');
    }

    /** Shaped as the live page had it on 2026-09-04 */
    private function realisticScript(): string
    {
        return <<<'HTML'
<script>
const nt_data = { publisher: "Cascadia Games", series: undefined, genres: "platformer|adventure" }
const nsuids = [ { nsuid: "70010000131705", gameTitle: "Cave Looters", gameTitleMaster: "Cave Looters",
productType: "gameFull", systemType: "nintendoswitch_downloadsoftware", releaseDate: "24/07/2026",
iseShopGame: true } ];
</script>
HTML;
    }

    public function testParsesTitleGenresDateAndNsuid()
    {
        $page = $this->pageWith($this->realisticScript());

        $this->assertEquals('Cave Looters', $page->getStoreTitle());
        $this->assertEquals('70010000131705', $page->getNsuid());
        $this->assertEquals('2026-07-24', $page->getReleaseDate());
    }

    public function testGenresAreCommaSeparatedNotPipeSeparated()
    {
        // The suggester splits on commas, the page uses pipes
        $page = $this->pageWith($this->realisticScript());

        $this->assertEquals('platformer, adventure', $page->getGenres());
    }

    public function testSingleGenreHasNoSeparator()
    {
        $page = $this->pageWith('<script>const nt_data = { genres: "puzzle" }</script>');

        $this->assertEquals('puzzle', $page->getGenres());
    }

    public function testEmptyGenresIsNullNotAnEmptyString()
    {
        $page = $this->pageWith('<script>const nt_data = { genres: "" }</script>');

        $this->assertNull($page->getGenres());
    }

    public function testMissingConfigReturnsNullRatherThanThrowing()
    {
        $page = $this->pageWith('<script>const something_else = 1;</script>');

        $this->assertNull($page->getStoreTitle());
        $this->assertNull($page->getGenres());
        $this->assertNull($page->getReleaseDate());
        $this->assertNull($page->getNsuid());
    }
}
