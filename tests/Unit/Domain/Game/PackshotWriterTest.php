<?php

namespace Tests\Unit\Domain\Game;

use App\Domain\Game\ImageResolver;
use App\Domain\Game\PackshotWriter;
use App\Models\Console;
use App\Models\Game;
use App\Models\GameImage;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Ingestion used to write packshots straight into public/img and set games.image_*, which
 * hard-coded local disk as the only possible destination. PackshotWriter is the seam that
 * lets a download land in object storage instead, chosen by config.
 *
 * These cover the two branches and the details that are easy to get wrong: per-type upserts
 * (so one download can't null the other packshot), the cache-busting timestamp, and the
 * shared naming that stops ingestion and the backfill writing two objects for one image.
 */
class PackshotWriterTest extends TestCase
{
    use DatabaseTransactions;

    private function writer(): PackshotWriter
    {
        return app(PackshotWriter::class);
    }

    private function makeGame(): Game
    {
        return Game::create([
            'title'          => 'Packshot Writer Test Game',
            'link_title'     => 'test-game',
            'console_id'     => Console::ID_SWITCH_2,
            'eu_is_released' => 0,
        ]);
    }

    private function tempFile(string $name = 'hdr-1-test-game.jpg'): string
    {
        $path = storage_path('/tmp/') . $name;
        file_put_contents($path, 'image-bytes');
        return $path;
    }

    public function test_defaults_to_legacy_when_config_is_unset()
    {
        config(['packshots.default_location' => null]);

        $this->assertEquals(PackshotWriter::LOCATION_LEGACY, $this->writer()->defaultLocation());
    }

    /**
     * Anything that isn't exactly 'spaces' must mean legacy. A typo in prod .env should leave
     * ingestion where it already worked, not send writes somewhere half-configured.
     */
    public function test_an_unrecognised_config_value_falls_back_to_legacy()
    {
        config(['packshots.default_location' => 'Spaces ']);

        $this->assertEquals(PackshotWriter::LOCATION_LEGACY, $this->writer()->defaultLocation());
    }

    public function test_spaces_write_puts_the_object_and_records_the_row()
    {
        Storage::fake('packshots');
        config(['packshots.default_location' => 'spaces']);

        $game = $this->makeGame();
        $filename = $this->writer()->store(
            $game, ImageResolver::TYPE_HEADER, $this->tempFile(), 'hdr-1-test-game.jpg'
        );

        $this->assertEquals("{$game->id}-test-game.jpg", $filename);
        Storage::disk('packshots')->assertExists("switch-2/header/{$filename}");

        $image = GameImage::where('game_id', $game->id)->first();
        $this->assertEquals(GameImage::LOCATION_SPACES, $image->location);
        $this->assertEquals($filename, $image->header_filename);
        $this->assertNotNull($image->header_updated_at);
    }

    /**
     * The legacy column must stay untouched under `spaces`. If ingestion set it, the resolver's
     * legacy fallback would start naming a file that was never written locally - a broken image
     * that only appears once the object storage lookup misses.
     */
    public function test_spaces_write_does_not_populate_the_legacy_column()
    {
        Storage::fake('packshots');
        config(['packshots.default_location' => 'spaces']);

        $game = $this->makeGame();
        $this->writer()->store(
            $game, ImageResolver::TYPE_HEADER, $this->tempFile(), 'hdr-1-test-game.jpg'
        );

        $this->assertNull($game->fresh()->image_header);
    }

    /**
     * Ingestion can legitimately fetch a header without a square (or replace just one via the
     * override-URL flow). Writing both filenames together - as the backfill does - would null
     * the packshot that wasn't downloaded.
     */
    public function test_writing_one_type_leaves_the_other_intact()
    {
        Storage::fake('packshots');
        config(['packshots.default_location' => 'spaces']);

        $game = $this->makeGame();
        GameImage::create([
            'game_id'         => $game->id,
            'square_filename' => 'existing-square.jpg',
            'location'        => GameImage::LOCATION_SPACES,
        ]);

        $this->writer()->store(
            $game, ImageResolver::TYPE_HEADER, $this->tempFile(), 'hdr-1-test-game.jpg'
        );

        $image = GameImage::where('game_id', $game->id)->first();
        $this->assertEquals('existing-square.jpg', $image->square_filename);
        $this->assertEquals("{$game->id}-test-game.jpg", $image->header_filename);
    }

    /**
     * A re-download keeps the same filename, so the object URL never changes and Cloudflare
     * would serve the old image indefinitely. spacesUrl() appends ?v={updated_at}, so bumping
     * the timestamp is the only thing that makes a replaced packshot actually appear.
     */
    public function test_rewriting_the_same_packshot_bumps_the_cache_busting_timestamp()
    {
        Storage::fake('packshots');
        config(['packshots.default_location' => 'spaces']);

        $game = $this->makeGame();
        $this->writer()->store(
            $game, ImageResolver::TYPE_HEADER, $this->tempFile(), 'hdr-1-test-game.jpg'
        );
        $first = GameImage::where('game_id', $game->id)->first()->header_updated_at;

        $this->travel(5)->minutes();

        $this->writer()->store(
            $game, ImageResolver::TYPE_HEADER, $this->tempFile(), 'hdr-1-test-game.jpg'
        );
        $second = GameImage::where('game_id', $game->id)->first()->header_updated_at;

        $this->assertTrue($second->greaterThan($first));
    }

    /**
     * Ingestion and ImageStorageMigrator must derive the same object name, or re-downloading a
     * migrated game writes a second object beside the first and the original leaks.
     */
    public function test_ingestion_naming_matches_the_backfill_convention()
    {
        Storage::fake('packshots');
        config(['packshots.default_location' => 'spaces']);

        $game = $this->makeGame();
        $resolver = app(ImageResolver::class);

        $written = $this->writer()->store(
            $game, ImageResolver::TYPE_HEADER, $this->tempFile(), 'hdr-9999-test-game-260720.jpg'
        );

        $this->assertEquals(
            $resolver->targetFilename($game, 'hdr-9999-test-game-260720.jpg'),
            $written
        );
    }

    public function test_legacy_write_moves_the_file_and_sets_the_column()
    {
        config(['packshots.default_location' => 'legacy']);

        $game = $this->makeGame();
        $temp = $this->tempFile('hdr-legacy-test.jpg');

        $filename = $this->writer()->store(
            $game, ImageResolver::TYPE_HEADER, $temp, 'hdr-legacy-test.jpg'
        );

        $destination = public_path('/img/ps-header/' . $filename);

        $this->assertEquals('hdr-legacy-test.jpg', $filename);
        $this->assertFileExists($destination);
        $this->assertEquals('hdr-legacy-test.jpg', $game->fresh()->image_header);
        $this->assertFileDoesNotExist($temp);

        unlink($destination);
    }

    public function test_legacy_write_creates_no_game_images_row()
    {
        config(['packshots.default_location' => 'legacy']);

        $game = $this->makeGame();
        $filename = $this->writer()->store(
            $game, ImageResolver::TYPE_HEADER, $this->tempFile('hdr-legacy-2.jpg'), 'hdr-legacy-2.jpg'
        );

        $this->assertDatabaseMissing('game_images', ['game_id' => $game->id]);

        unlink(public_path('/img/ps-header/' . $filename));
    }
}
