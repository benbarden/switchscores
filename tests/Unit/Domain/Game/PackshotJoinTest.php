<?php

namespace Tests\Unit\Domain\Game;

use App\Domain\Game\ImageResolver;
use App\Domain\Game\PackshotJoin;
use App\Models\Console;
use App\Models\GameImage;
use Tests\TestCase;

/**
 * Raw `DB::table('games')` rows used to resolve packshots via the legacy path unconditionally,
 * because ImageResolver reads `game_images` through an Eloquent relation that raw rows do not
 * have. That made the on-sale tabs and tag pages permanently legacy-only — invisible while
 * legacy files exist, broken images once ingestion writes only to object storage.
 *
 * These cover the raw-row path end to end: hydration from the aliased join columns, and the
 * resolver returning an object-storage URL for a raw row rather than falling back.
 */
class PackshotJoinTest extends TestCase
{
    private function rawRow(array $overrides = []): object
    {
        return (object) array_merge([
            'id'           => 4242,
            'console_id'   => Console::ID_SWITCH_2,
            'link_title'   => 'test-game',
            'image_square' => 'sq-4242-test-game.jpg',
            'image_header' => 'hdr-4242-test-game.jpg',

            PackshotJoin::ALIAS_LOCATION          => GameImage::LOCATION_SPACES,
            PackshotJoin::ALIAS_SQUARE_FILENAME   => '4242-test-game.jpg',
            PackshotJoin::ALIAS_HEADER_FILENAME   => '4242-test-game.jpg',
            PackshotJoin::ALIAS_SQUARE_UPDATED_AT => '2026-07-20 10:00:00',
            PackshotJoin::ALIAS_HEADER_UPDATED_AT => '2026-07-20 10:00:00',
        ], $overrides);
    }

    public function test_hydrates_a_game_image_from_the_aliased_join_columns()
    {
        $image = PackshotJoin::hydrate($this->rawRow());

        $this->assertInstanceOf(GameImage::class, $image);
        $this->assertEquals(GameImage::LOCATION_SPACES, $image->location);
        $this->assertEquals('4242-test-game.jpg', $image->square_filename);
    }

    /**
     * spacesUrl() calls ->timestamp on these for cache-busting, so the datetime casts have to
     * survive hydration. A plain constructor would leave them as strings and fatal.
     */
    public function test_updated_at_columns_are_cast_to_dates_not_strings()
    {
        $image = PackshotJoin::hydrate($this->rawRow());

        $this->assertNotNull($image->square_updated_at);
        $this->assertIsInt($image->square_updated_at->timestamp);
    }

    public function test_returns_null_when_the_join_was_not_applied()
    {
        $row = (object) ['id' => 1, 'image_header' => 'hdr-1-game.jpg'];

        $this->assertNull(PackshotJoin::hydrate($row));
    }

    public function test_returns_null_when_the_game_has_no_game_images_row()
    {
        // Left join matched nothing, so every aliased column comes back null.
        $row = $this->rawRow([PackshotJoin::ALIAS_LOCATION => null]);

        $this->assertNull(PackshotJoin::hydrate($row));
    }

    public function test_resolver_serves_object_storage_for_a_raw_row_with_the_join()
    {
        $url = app(ImageResolver::class)->url($this->rawRow(), ImageResolver::TYPE_HEADER);

        $this->assertStringNotContainsString('/img/ps-header/', $url);
        $this->assertStringContainsString('4242-test-game.jpg', $url);
    }

    public function test_resolver_still_falls_back_to_legacy_for_a_raw_row_without_the_join()
    {
        $row = (object) ['id' => 1, 'console_id' => 1, 'image_header' => 'hdr-1-game.jpg'];

        $url = app(ImageResolver::class)->url($row, ImageResolver::TYPE_HEADER);

        $this->assertEquals('/img/ps-header/hdr-1-game.jpg', $url);
    }

    public function test_resolver_falls_back_to_legacy_when_location_is_still_legacy()
    {
        $row = $this->rawRow([PackshotJoin::ALIAS_LOCATION => GameImage::LOCATION_LEGACY]);

        $url = app(ImageResolver::class)->url($row, ImageResolver::TYPE_HEADER);

        $this->assertEquals('/img/ps-header/hdr-4242-test-game.jpg', $url);
    }
}
