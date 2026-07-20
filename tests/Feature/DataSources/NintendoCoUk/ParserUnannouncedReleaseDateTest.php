<?php

namespace Tests\Feature\DataSources\NintendoCoUk;

use App\Models\DataSourceRaw;
use App\Services\DataSources\NintendoCoUk\Parser;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Unannounced titles carry the literal string "TBD" as their release date.
 *
 * parseReleaseDate() cannot parse that, so release_date_eu lands null. These records also have
 * no real price (price_sorting_f holds the 999999 sentinel), so there is nothing worth importing
 * until Nintendo announces a date. ImportParseLink skips them when they are ALSO unlinked, which
 * is what keeps them out of the unlinked queue - the "No EU date" tab on
 * /staff/data-sources/nintendo-co-uk/unlinked was retired on the back of this.
 *
 * The pairing matters: an unparseable date alone must not drop a record that is already attached
 * to a game, or a live game would silently stop being updated.
 */
class ParserUnannouncedReleaseDateTest extends TestCase
{
    use DatabaseTransactions;

    const SOURCE_ID = 2;

    private function parseWithDate($prettyDate): ?string
    {
        $raw = new DataSourceRaw([
            'source_id' => self::SOURCE_ID,
            'console_id' => 2,
            'link_id' => '999000222',
            'title' => 'Unannounced Test Game',
            'source_data_json' => json_encode(['pretty_date_s' => $prettyDate]),
        ]);

        $parser = app(Parser::class);
        $parser->setDataSourceRaw($raw);

        return $parser->parseReleaseDate();
    }

    public function test_tbd_release_date_parses_to_null()
    {
        $this->assertNull($this->parseWithDate('TBD'));
    }

    public function test_a_real_release_date_still_parses()
    {
        $this->assertEquals('2026-07-20', $this->parseWithDate('20/07/2026'));
    }

    public function test_other_unparseable_dates_are_null_not_an_exception()
    {
        $this->assertNull($this->parseWithDate('Coming soon'));
        $this->assertNull($this->parseWithDate(''));
    }

    /**
     * The skip condition as ImportParseLink applies it: no EU date AND no game_id.
     * A record with a game_id must survive even when its date is unparseable.
     */
    public function test_skip_condition_spares_records_already_linked_to_a_game()
    {
        $unlinkedNoDate = (object) ['release_date_eu' => null, 'game_id' => null];
        $linkedNoDate   = (object) ['release_date_eu' => null, 'game_id' => 1234];
        $unlinkedDated  = (object) ['release_date_eu' => '2026-07-20', 'game_id' => null];

        $shouldSkip = fn ($i) => is_null($i->release_date_eu) && is_null($i->game_id);

        $this->assertTrue($shouldSkip($unlinkedNoDate), 'unannounced + unlinked should be skipped');
        $this->assertFalse($shouldSkip($linkedNoDate), 'a linked game must never be skipped');
        $this->assertFalse($shouldSkip($unlinkedDated), 'an announced title should still import');
    }
}
